<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagGroupSpendingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FamilyGroup $group;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => bcrypt('secret'),
        ]);

        $this->group = FamilyGroup::create([
            'name' => 'Familia Test',
            'owner_id' => $this->user->id,
        ]);
        $this->group->members()->attach($this->user->id, ['role' => 'owner', 'joined_at' => now()]);

        $this->account = Account::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'name' => 'Efectivo',
            'type' => 'cash',
            'currency' => 'ARS',
            'initial_balance' => 0,
            'is_active' => true,
        ]);
    }

    private function tag(string $name): Tag
    {
        return Tag::create(['family_group_id' => $this->group->id, 'name' => $name, 'color' => '#888888']);
    }

    private function tagGroup(string $name, array $tagIds): TagGroup
    {
        $tg = TagGroup::create(['family_group_id' => $this->group->id, 'name' => $name, 'color' => '#123456']);
        $tg->tags()->attach($tagIds);

        return $tg;
    }

    private function expenseWithTags(float $amount, array $tagIds, string $date): Transaction
    {
        $tx = Transaction::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'type' => 'expense',
            'amount' => $amount,
            'currency' => 'ARS',
            'date' => $date,
        ]);
        $tx->tags()->attach($tagIds);

        return $tx;
    }

    /**
     * Una transacción con DOS etiquetas del MISMO grupo debe sumarse una sola
     * vez en ese grupo (no dos veces).
     */
    public function test_transaction_with_two_tags_of_same_group_counts_once(): void
    {
        $t1 = $this->tag('Nafta');
        $t2 = $this->tag('Peaje');
        $group = $this->tagGroup('Auto', [$t1->id, $t2->id]);

        // Un solo gasto de 10.000 con AMBAS etiquetas del grupo Auto.
        $this->expenseWithTags(10000, [$t1->id, $t2->id], now()->format('Y-m-d'));

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $stats = collect($response->viewData('tagGroupStats'));
        $auto = $stats->firstWhere('id', $group->id);

        // Debe contar el gasto una sola vez: 10.000, count = 1 (no 20.000 / 2).
        $this->assertSame(10000.0, (float) $auto['expense']);
        $this->assertSame(1, $auto['count']);
    }

    /**
     * Documenta el comportamiento entre grupos: una transacción cuyas etiquetas
     * caen en dos grupos distintos aparece (una vez) en cada grupo.
     */
    public function test_transaction_spanning_two_groups_appears_in_each_once(): void
    {
        $tAuto = $this->tag('Nafta');
        $tOcio = $this->tag('Cine');
        $groupAuto = $this->tagGroup('Auto', [$tAuto->id]);
        $groupOcio = $this->tagGroup('Ocio', [$tOcio->id]);

        // Gasto único de 5.000 con una etiqueta de cada grupo.
        $this->expenseWithTags(5000, [$tAuto->id, $tOcio->id], now()->format('Y-m-d'));

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $stats = collect($response->viewData('tagGroupStats'));

        // Aparece una vez en cada grupo (solapamiento esperado entre grupos).
        $this->assertSame(5000.0, (float) $stats->firstWhere('id', $groupAuto->id)['expense']);
        $this->assertSame(1, $stats->firstWhere('id', $groupAuto->id)['count']);
        $this->assertSame(5000.0, (float) $stats->firstWhere('id', $groupOcio->id)['expense']);
        $this->assertSame(1, $stats->firstWhere('id', $groupOcio->id)['count']);

        // El total etiquetado base cuenta la transacción una sola vez (5.000),
        // aunque la suma de los grupos (10.000) la duplique por el solapamiento.
        $this->assertSame(5000.0, round((float) $response->viewData('totalTaggedExpenseBase'), 2));
    }
}
