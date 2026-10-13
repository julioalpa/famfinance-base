<?php

namespace Tests\Unit;

use App\Models\ExchangeRate;
use App\Services\ExchangeRateResolver;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExchangeRateResolverTest extends TestCase
{
    private function rate(string $date, float $rate): ExchangeRate
    {
        return new ExchangeRate([
            'from_currency' => 'USD',
            'to_currency' => 'ARS',
            'rate' => $rate,
            'date' => $date,
        ]);
    }

    public function test_returns_rate_vigente_at_given_date(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
            $this->rate('2026-03-01', 1200),
            $this->rate('2026-06-01', 1500),
        ]);

        // Exactamente en la fecha de una tasa
        $this->assertSame(1200.0, $resolver->rateOn('2026-03-01'));
        // Entre dos tasas: toma la anterior vigente
        $this->assertSame(1200.0, $resolver->rateOn('2026-04-15'));
        $this->assertSame(1500.0, $resolver->rateOn('2026-07-20'));
        $this->assertSame(1000.0, $resolver->rateOn('2026-02-10'));
    }

    public function test_dates_before_first_rate_use_oldest(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-03-01', 1200),
            $this->rate('2026-06-01', 1500),
        ]);

        $this->assertSame(1200.0, $resolver->rateOn('2025-12-01'));
    }

    public function test_convert_uses_date_specific_rate(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
            $this->rate('2026-06-01', 1500),
        ]);

        // 10 USD en enero → 10.000 ARS; en julio → 15.000 ARS
        $this->assertSame(10000.0, $resolver->convert(10, 'USD', '2026-01-15'));
        $this->assertSame(15000.0, $resolver->convert(10, 'USD', '2026-07-15'));
    }

    public function test_ars_amounts_are_not_converted(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
        ]);

        $this->assertSame(500.0, $resolver->convert(500, 'ARS', '2026-05-01'));
    }

    public function test_no_rates_returns_amount_unchanged(): void
    {
        $resolver = new ExchangeRateResolver([]);

        $this->assertFalse($resolver->hasRates());
        $this->assertNull($resolver->rateOn('2026-01-01'));
        $this->assertSame(42.0, $resolver->convert(42, 'USD', '2026-01-01'));
    }

    public function test_accepts_carbon_dates(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
            $this->rate('2026-06-01', 1500),
        ]);

        $this->assertSame(1500.0, $resolver->rateOn(Carbon::parse('2026-08-01')));
    }

    public function test_convert_between_usd_to_ars_by_date(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
            $this->rate('2026-06-01', 1500),
        ]);

        $this->assertSame(10000.0, $resolver->convertBetween(10, 'USD', 'ARS', '2026-01-15'));
        $this->assertSame(15000.0, $resolver->convertBetween(10, 'USD', 'ARS', '2026-07-15'));
    }

    public function test_convert_between_ars_to_usd_by_date(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
            $this->rate('2026-06-01', 1500),
        ]);

        // 10.000 ARS en enero → 10 USD; en julio → 6,667 USD aprox.
        $this->assertSame(10.0, $resolver->convertBetween(10000, 'ARS', 'USD', '2026-01-15'));
        $this->assertEqualsWithDelta(6.6667, $resolver->convertBetween(10000, 'ARS', 'USD', '2026-07-15'), 0.0001);
    }

    public function test_convert_between_same_currency_is_noop(): void
    {
        $resolver = new ExchangeRateResolver([
            $this->rate('2026-01-01', 1000),
        ]);

        $this->assertSame(500.0, $resolver->convertBetween(500, 'ARS', 'ARS', '2026-05-01'));
        $this->assertSame(7.0, $resolver->convertBetween(7, 'USD', 'USD', '2026-05-01'));
    }

    public function test_convert_between_without_rates_returns_amount(): void
    {
        $resolver = new ExchangeRateResolver([]);

        $this->assertSame(42.0, $resolver->convertBetween(42, 'USD', 'ARS', '2026-01-01'));
        $this->assertSame(42.0, $resolver->convertBetween(42, 'ARS', 'USD', '2026-01-01'));
    }
}
