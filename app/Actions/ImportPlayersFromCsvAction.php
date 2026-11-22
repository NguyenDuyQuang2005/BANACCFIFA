<?php

namespace App\Actions;

use App\Models\Player;
use App\Models\PlayerSeason;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Imports players from a CSV (comma or semicolon, UTF-8, header row required).
 * Columns: name, season_code, position, ovr, pace, stamina, stars, reset_odd_minute, reset_even_minute.
 * A row with the same name + season + position updates the existing player.
 */
class ImportPlayersFromCsvAction
{
    public const COLUMNS = ['name', 'season_code', 'position', 'ovr', 'pace', 'stamina', 'stars', 'reset_odd_minute', 'reset_even_minute'];

    /**
     * @return array{created: int, updated: int, errors: list<string>}
     */
    public function handle(string $path): array
    {
        $handle = fopen($path, 'r');
        $firstLine = (string) fgets($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $header = array_map(
            fn (string $column): string => strtolower(trim(str_replace("\u{FEFF}", '', $column))),
            str_getcsv(trim($firstLine), $delimiter),
        );

        $missing = array_diff(['name', 'position', 'ovr'], $header);
        if ($missing !== []) {
            fclose($handle);

            return ['created' => 0, 'updated' => 0, 'errors' => ['Thiếu cột bắt buộc: '.implode(', ', $missing).'.']];
        }

        $seasons = PlayerSeason::query()->pluck('id', 'code')->mapWithKeys(fn ($id, $code): array => [mb_strtoupper($code) => $id]);
        $positions = config('tools.player_positions', []);
        $result = ['created' => 0, 'updated' => 0, 'errors' => []];
        $line = 1;

        DB::transaction(function () use ($handle, $delimiter, $header, $seasons, $positions, &$result, &$line): void {
            while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
                $line++;

                if ($values === [null] || trim(implode('', $values)) === '') {
                    continue;
                }

                $row = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), null));
                $row = array_map(fn ($value) => is_string($value) && trim($value) !== '' ? trim($value) : null, $row);
                $row['position'] = isset($row['position']) ? mb_strtoupper($row['position']) : null;
                $seasonCode = isset($row['season_code']) ? mb_strtoupper($row['season_code']) : null;

                $validator = Validator::make($row, [
                    'name' => ['required', 'string', 'max:120'],
                    'position' => ['required', Rule::in($positions)],
                    'ovr' => ['required', 'integer', 'min:1', 'max:300'],
                    'pace' => ['nullable', 'integer', 'min:1', 'max:300'],
                    'stamina' => ['nullable', 'integer', 'min:1', 'max:300'],
                    'stars' => ['nullable', 'integer', 'min:1', 'max:5'],
                    'reset_odd_minute' => ['nullable', 'integer', 'min:0', 'max:59'],
                    'reset_even_minute' => ['nullable', 'integer', 'min:0', 'max:59'],
                ]);

                if ($seasonCode !== null && ! $seasons->has($seasonCode)) {
                    $result['errors'][] = "Dòng {$line}: mùa thẻ \"{$seasonCode}\" chưa có, hãy tạo mùa thẻ trước.";

                    continue;
                }

                if ($validator->fails()) {
                    $result['errors'][] = "Dòng {$line}: ".$validator->errors()->first();

                    continue;
                }

                $seasonId = $seasonCode !== null ? $seasons->get($seasonCode) : null;

                $player = Player::query()->firstOrNew([
                    'name' => $row['name'],
                    'player_season_id' => $seasonId,
                    'position' => $row['position'],
                ]);
                $isNew = ! $player->exists;

                $player->fill([
                    'ovr' => (int) $row['ovr'],
                    'pace' => $row['pace'] ?? null,
                    'stamina' => $row['stamina'] ?? null,
                    'stars' => $row['stars'] ?? null,
                    'reset_odd_minute' => $row['reset_odd_minute'] ?? null,
                    'reset_even_minute' => $row['reset_even_minute'] ?? null,
                ])->save();

                $result[$isNew ? 'created' : 'updated']++;
            }
        });

        fclose($handle);

        return $result;
    }
}
