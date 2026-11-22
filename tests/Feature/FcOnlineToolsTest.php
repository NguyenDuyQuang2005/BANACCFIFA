<?php

namespace Tests\Feature;

use App\Models\CoachSkill;
use App\Models\Player;
use App\Models\PlayerSeason;
use App\Models\SkillMove;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FcOnlineToolsTest extends TestCase
{
    public function test_all_six_tool_pages_render(): void
    {
        foreach (['tinh-thue', 'dao-tao-ovr', 'ky-thuat', 'ranking', 'du-lieu-cau-thu', 'gio-reset'] as $slug) {
            $this->get(route('tools.show', $slug))->assertOk();
        }

        $this->get(route('tools.index'))->assertOk()->assertSee('Ranking kỹ năng HLV');
    }

    public function test_ovr_page_ships_position_weights_to_the_browser(): void
    {
        $this->get(route('tools.show', 'dao-tao-ovr'))
            ->assertOk()
            ->assertSee('data-ovr-config', false)
            ->assertSee('LS / ST / RS')
            ->assertSee('"finishing":18', false);
    }

    public function test_skill_lookup_lists_visible_skills_with_keys(): void
    {
        SkillMove::factory()->create(['name' => 'Juggling dung yen', 'level' => 1, 'keyboard_keys' => 'Giữ C + Nhảy Z']);
        SkillMove::factory()->create(['name' => 'Ky thuat an', 'is_visible' => false]);

        $this->get(route('tools.show', 'ky-thuat'))
            ->assertOk()
            ->assertSee('Juggling dung yen')
            ->assertSee('Giữ C + Nhảy Z')
            ->assertDontSee('Ky thuat an');
    }

    public function test_coach_ranking_orders_by_usage(): void
    {
        CoachSkill::factory()->create(['name' => 'Ky nang thap', 'usage_percent' => 1.5]);
        CoachSkill::factory()->create(['name' => 'Ky nang cao', 'usage_percent' => 22.2]);

        $this->get(route('tools.show', 'ranking'))
            ->assertOk()
            ->assertSeeInOrder(['#1', 'Ky nang cao', '22.20%', '#2', 'Ky nang thap']);
    }

    public function test_player_data_filters_and_compares(): void
    {
        $icon = PlayerSeason::factory()->create(['code' => 'ICON', 'name' => 'ICON']);
        $ronaldo = Player::factory()->create(['name' => 'Ronaldo Test', 'position' => 'ST', 'ovr' => 136, 'pace' => 143, 'player_season_id' => $icon->id]);
        $messi = Player::factory()->create(['name' => 'Messi Test', 'position' => 'RW', 'ovr' => 135, 'pace' => 136]);
        Player::factory()->create(['name' => 'Hidden Test', 'is_visible' => false]);

        $this->get(route('tools.show', ['du-lieu-cau-thu', 'q' => 'ronaldo']))
            ->assertSee('Ronaldo Test')->assertDontSee('Messi Test')->assertDontSee('Hidden Test');

        $this->get(route('tools.show', ['du-lieu-cau-thu', 'position' => 'RW']))
            ->assertSee('Messi Test')->assertDontSee('Ronaldo Test');

        $this->get(route('tools.show', ['du-lieu-cau-thu', 'season' => $icon->id]))
            ->assertSee('Ronaldo Test')->assertDontSee('Messi Test');

        $this->get(route('tools.show', ['du-lieu-cau-thu', 'compare' => [$ronaldo->id, $messi->id]]))
            ->assertOk()
            ->assertSee('So sánh 2 cầu thủ')
            ->assertSee('143');

        $this->get(route('tools.player', $ronaldo))->assertOk()->assertSee('Ronaldo Test');
        $this->get(route('tools.player', Player::query()->where('name', 'Hidden Test')->first()))->assertNotFound();
    }

    public function test_reset_schedule_label_and_next_reset(): void
    {
        $player = Player::factory()->make(['reset_odd_minute' => 55, 'reset_even_minute' => 25]);
        $tz = 'Asia/Ho_Chi_Minh';

        $this->assertSame('Lẻ 55 - Chẵn 25', $player->resetLabel());
        $this->assertSame('16:25', $player->nextResetAt(CarbonImmutable::create(2026, 11, 22, 16, 10, 0, $tz))->format('H:i'));
        $this->assertSame('17:55', $player->nextResetAt(CarbonImmutable::create(2026, 11, 22, 16, 39, 0, $tz))->format('H:i'));
        $this->assertSame('18:25', $player->nextResetAt(CarbonImmutable::create(2026, 11, 22, 17, 56, 0, $tz))->format('H:i'));

        $oddOnly = Player::factory()->make(['reset_odd_minute' => 1, 'reset_even_minute' => null]);
        $this->assertSame('Lẻ 01', $oddOnly->resetLabel());
        $this->assertSame('17:01', $oddOnly->nextResetAt(CarbonImmutable::create(2026, 11, 22, 16, 30, 0, $tz))->format('H:i'));

        $this->assertNull(Player::factory()->make()->nextResetAt());
    }

    public function test_reset_page_shows_schedule(): void
    {
        Player::factory()->create(['name' => 'Maradona Test', 'reset_odd_minute' => 55, 'reset_even_minute' => 25]);

        $this->get(route('tools.show', 'gio-reset'))
            ->assertOk()
            ->assertSee('Maradona Test')
            ->assertSee('Lẻ 55 - Chẵn 25')
            ->assertSee('data-reset-clock', false);
    }

    public function test_manager_can_manage_tool_data_and_member_cannot(): void
    {
        Storage::fake('public');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->post(route('admin.tool-skills.store'), [
            'name' => 'Step Over', 'level' => 2, 'keyboard_keys' => 'SHIFT + ↑ + →', 'sort_order' => 0, 'is_visible' => '1',
            'media' => UploadedFile::fake()->create('step.gif', 500, 'image/gif'),
        ])->assertSessionHasNoErrors();
        $this->assertNotNull(SkillMove::query()->value('media_path'));

        $this->actingAs($manager)->post(route('admin.tool-coach-skills.store'), [
            'name' => 'Kha nang kheo leo', 'usage_percent' => 22.2, 'is_visible' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAs($manager)->post(route('admin.tool-seasons.store'), ['code' => 'icon', 'name' => 'ICON', 'sort_order' => 0])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('player_seasons', ['code' => 'ICON']);

        $this->actingAs($manager)->post(route('admin.tool-players.store'), [
            'name' => 'Pele', 'player_season_id' => PlayerSeason::query()->value('id'), 'position' => 'cf', 'ovr' => 127,
            'reset_odd_minute' => 55, 'reset_even_minute' => 25, 'is_visible' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('players', ['name' => 'Pele', 'position' => 'CF', 'reset_odd_minute' => 55]);

        foreach (['admin.tool-players.index', 'admin.tool-skills.index', 'admin.tool-coach-skills.index'] as $routeName) {
            $this->actingAs($manager)->get(route($routeName))->assertOk();
            $this->actingAs(User::factory()->create())->get(route($routeName))->assertForbidden();
        }

        $this->assertDatabaseHas('activity_logs', ['action' => 'player.created']);
    }

    public function test_csv_import_creates_updates_and_reports_bad_rows(): void
    {
        $admin = User::factory()->admin()->create();
        PlayerSeason::factory()->create(['code' => 'ICON']);
        Player::factory()->create(['name' => 'Ronaldo', 'position' => 'ST', 'ovr' => 100, 'player_season_id' => PlayerSeason::query()->value('id')]);

        $csv = "\u{FEFF}name;season_code;position;ovr;pace;stamina;stars;reset_odd_minute;reset_even_minute\n"
            ."Ronaldo;icon;st;136;143;138;5;55;25\n"
            ."Messi;;RW;135;136;135;4;;\n"
            ."Sai vi tri;;XYZ;120;;;;;\n"
            ."Sai mua;NOPE;ST;120;;;;;\n";

        $this->actingAs($admin)
            ->post(route('admin.tool-players.import'), ['file' => UploadedFile::fake()->createWithContent('players.csv', $csv)])
            ->assertSessionHas('status', 'Nhập xong: thêm 1, cập nhật 1, lỗi 2.')
            ->assertSessionHas('importErrors', fn (array $errors): bool => count($errors) === 2);

        $this->assertSame(136, Player::query()->where('name', 'Ronaldo')->value('ovr'));
        $this->assertSame(55, Player::query()->where('name', 'Ronaldo')->value('reset_odd_minute'));
        $this->assertDatabaseHas('players', ['name' => 'Messi', 'position' => 'RW', 'player_season_id' => null]);
        $this->assertDatabaseCount('players', 2);

        $this->actingAs($admin)->get(route('admin.tool-players.template'))->assertOk()->assertDownload('mau-nhap-cau-thu.csv');
    }
}
