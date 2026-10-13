<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Resuelve el tipo de cambio USD→ARS vigente a una fecha dada.
 *
 * Problema que resuelve: hasta ahora los reportes usaban una única tasa
 * (la más reciente) para valorizar transacciones USD de cualquier mes, lo
 * que distorsiona las comparativas históricas en contextos de alta
 * devaluación. Este resolver toma, para cada transacción, la tasa vigente
 * a su fecha (la más reciente cuya `date` sea <= a la fecha de la operación).
 *
 * Reglas:
 *  - Si la fecha es anterior a la primera tasa cargada, se usa la tasa más
 *    antigua disponible (mejor aproximación que no convertir).
 *  - Si no hay tasas cargadas, no se convierte (se devuelve el monto tal cual).
 *
 * Uso típico (cachear por grupo, una sola carga de DB):
 *   $resolver = ExchangeRateResolver::forGroup($groupId);
 *   $ars = $resolver->convert($amountUsd, 'USD', $transaction->date);
 */
class ExchangeRateResolver
{
    /**
     * Tasas ordenadas ascendentemente por fecha.
     *
     * @var Collection<int, ExchangeRate>
     */
    private Collection $ratesAsc;

    /**
     * @param  iterable<ExchangeRate>  $rates
     */
    public function __construct(iterable $rates)
    {
        $this->ratesAsc = collect($rates)
            ->sortBy(fn (ExchangeRate $r) => $r->date instanceof Carbon
                ? $r->date->toDateString()
                : (string) $r->date)
            ->values();
    }

    /**
     * Construye el resolver cargando todas las tasas del grupo una sola vez.
     */
    public static function forGroup(int $familyGroupId): self
    {
        $rates = ExchangeRate::where('family_group_id', $familyGroupId)
            ->orderBy('date')
            ->get();

        return new self($rates);
    }

    /**
     * ¿Hay alguna tasa cargada?
     */
    public function hasRates(): bool
    {
        return $this->ratesAsc->isNotEmpty();
    }

    /**
     * Devuelve la tasa (float) vigente a la fecha indicada, o null si no hay
     * ninguna tasa cargada.
     */
    public function rateOn(Carbon|string|null $date): ?float
    {
        if ($this->ratesAsc->isEmpty()) {
            return null;
        }

        $target = $this->normalizeDate($date);

        // Última tasa cuya fecha sea <= target.
        $vigente = $this->ratesAsc
            ->filter(fn (ExchangeRate $r) => $this->rateDate($r) <= $target)
            ->last();

        // Si la fecha es anterior a la primera tasa, usar la más antigua.
        $vigente ??= $this->ratesAsc->first();

        return (float) $vigente->rate;
    }

    /**
     * Convierte un monto en la moneda origen a ARS usando la tasa vigente a la
     * fecha. Si la moneda ya es ARS, o no hay tasas cargadas, devuelve el monto
     * sin modificar.
     */
    public function convert(float $amount, string $from, Carbon|string|null $date): float
    {
        if ($from === 'ARS') {
            return $amount;
        }

        $rate = $this->rateOn($date);
        if ($rate === null) {
            return $amount;
        }

        // Las tasas se almacenan siempre como USD→ARS (1 USD = rate ARS).
        if ($from === 'USD') {
            return $amount * $rate;
        }

        // Moneda desconocida: no convertir.
        return $amount;
    }

    /**
     * Convierte un monto entre dos monedas cualesquiera (ARS/USD) usando la
     * tasa vigente a la fecha. Útil para valorizar en la moneda nativa de una
     * cuenta (p. ej. un movimiento USD dentro de una cuenta ARS, o un
     * movimiento ARS dentro de una cuenta USD).
     *
     * Si origen y destino coinciden, o no hay tasas cargadas, devuelve el monto
     * sin modificar.
     */
    public function convertBetween(float $amount, string $from, string $to, Carbon|string|null $date): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->rateOn($date);
        if ($rate === null || $rate == 0.0) {
            return $amount;
        }

        // Tasas almacenadas como USD→ARS (1 USD = rate ARS).
        if ($from === 'USD' && $to === 'ARS') {
            return $amount * $rate;
        }
        if ($from === 'ARS' && $to === 'USD') {
            return $amount / $rate;
        }

        // Par de monedas no soportado: no convertir.
        return $amount;
    }

    private function normalizeDate(Carbon|string|null $date): string
    {
        if ($date === null) {
            return now()->toDateString();
        }
        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        return Carbon::parse($date)->toDateString();
    }

    private function rateDate(ExchangeRate $r): string
    {
        return $r->date instanceof Carbon
            ? $r->date->toDateString()
            : Carbon::parse((string) $r->date)->toDateString();
    }
}
