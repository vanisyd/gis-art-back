<?php

namespace Tests\Unit;

use App\Models\Driver;
use App\Models\DriverPayableTime;
use App\Models\DriverTrip;
use App\Repositories\DriverRepository;
use App\Services\DriverDataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DriverDataServiceTest extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public static function dataProviderForCalculatePayableTime(): array
    {
        return [
            [
                [
                    (object) [
                        'id' => 2,
                        'driver_id' => 1,
                        'pickup' => Carbon::parse('2022-01-01 09:40:00'),
                        'dropoff' => Carbon::parse('2022-01-01 11:00:00')
                    ],
                    (object) [
                        'id' => 1,
                        'driver_id' => 1,
                        'pickup' => Carbon::parse('2022-01-01 10:00:00'),
                        'dropoff' => Carbon::parse('2022-01-01 11:00:00')
                    ],
                    (object) [
                        'id' => 4,
                        'driver_id' => 2,
                        'pickup' => Carbon::parse('2022-01-01 10:00:00'),
                        'dropoff' => Carbon::parse('2022-01-01 11:00:00')
                    ],
                    (object) [
                        'id' => 3,
                        'driver_id' => 2,
                        'pickup' => Carbon::parse('2022-01-01 11:30:00'),
                        'dropoff' => Carbon::parse('2022-01-01 12:30:00')
                    ],
                ],
                [
                    ['driver_id' => 1, 'total_minutes_with_passenger' => 140],
                    ['driver_id' => 2, 'total_minutes_with_passenger' => 120]
                ]
            ],
        ];
    }

    #[DataProvider('dataProviderForCalculatePayableTime')]
    public function testCalculatesPayableTimeCorrectly(array $trips, array $expectedResult): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'csv');

        $mockRepository = Mockery::mock('overload:' . DriverRepository::class);
        $mockRepository->shouldReceive('getDriverTrips')->andReturn(collect($trips));
        $mockRepository->shouldReceive('createDriverPayableTime')
            ->andReturnUsing(function ($attrs) {
                return new DriverPayableTime($attrs);
            });

        $mockBuilder = Mockery::mock('overload:Illuminate\Database\Query\Builder');
        $mockBuilder->shouldReceive('truncate')->once();

        DB::shouldReceive('table')->with('driver_payable_times')->andReturn($mockBuilder);

        $service = app(DriverDataService::class);
        $result = $service->calculatePayableTime($filename);

        $this->assertEquals($expectedResult, $result->toArray());
    }

    public static function dataProviderForImportDriverData(): array
    {
        return [
            [
                [
                    ['id' => 1, 'driver_id' => 1, 'pickup' => '2022-01-01 10:00:00', 'dropoff' => '2022-01-01 11:00:00'],
                    ['id' => 2, 'driver_id' => 1, 'pickup' => '2022-01-01 10:20:00', 'dropoff' => '2022-01-01 11:00:00'],
                    ['id' => 3, 'driver_id' => 2, 'pickup' => '2022-01-01 11:30:00', 'dropoff' => '2022-01-01 12:30:00'],
                    ['id' => 4, 'driver_id' => 2, 'pickup' => '2022-01-01 10:00:00', 'dropoff' => '2022-01-01 11:00:00'],
                ],
                4
            ],
        ];
    }

    #[DataProvider('dataProviderForImportDriverData')]
    public function testImportsDriverDataCorrectly(array $trips, int $expectedCount): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'csv');
        $file = fopen($filename, 'w');
        fputcsv($file, ['id', 'driver_id', 'pickup', 'dropoff']);
        foreach ($trips as $trip) {
            fputcsv($file, $trip);
        }
        fclose($file);

        $mockRepository = Mockery::mock('overload:' . DriverRepository::class);
        $mockRepository->shouldReceive('createDriverTrip')->andReturn(true);
        $mockRepository->shouldReceive('createDriver')->andReturn(true);

        $service = app(DriverDataService::class);
        $count = $service->importDriverData($filename);

        $this->assertEquals($expectedCount, $count);
    }
}
