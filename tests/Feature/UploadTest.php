<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Wrong file extension
     *
     * @return void
     */
    public function test_wrong_extension()
    {
        $fileName = 'ng.txt';
        Storage::fake($fileName);

        $file = UploadedFile::fake()->create($fileName);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'errors' => ["Please input a CSV file"]
        ]);
    }

    public function test_no_data_file() {

        $fileName = 'no_data_file.csv';
        Storage::fake($fileName);

        $file = UploadedFile::fake()->create($fileName);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'errors' => ["There is no data"]
        ]);
    }

    public function test_no_data_but_have_header() {
        $fileName = 'no_data_but_have_header.csv';
        $header = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $content = $header;
        Storage::fake($fileName);

        $file = UploadedFile::fake()->createWithContent($fileName, $content);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'errors' => ["There is no data"]
        ]);
    }

    public function test_has_data_but_wrong_date_format() {
        $fileName = 'has_data_but_wrong_date_format.csv';
        $header = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $row1 = 'YYYY-01-01,city of london,91449,E09000001,17,,1';
        $content = implode(PHP_EOL, [ $header, $row1 ]);

        Storage::fake($fileName);

        $file = UploadedFile::fake()->createWithContent($fileName, $content);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'errors' => [
                '"Date" column format is wrong, YYYY-MM-DD'  
            ]
        ]);
    }

    public function test_has_data_but_wrong_no_of_crimes_format() {
        $fileName = 'has_data_but_wrong_no_of_crimes_format.csv';
        $header = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $row1 = '1995-01-01,city of london,91449,E09000001,17,gdfgfd,1';
        $content = implode(PHP_EOL, [ $header, $row1 ]);

        Storage::fake($fileName);

        $file = UploadedFile::fake()->createWithContent($fileName, $content);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'errors' => [
                '"Number of crimes" column format must be a number'  
            ]
        ]);
    }

    public function test_has_data_but_wrong_houses_sold_format() {
        $fileName = 'has_data_but_wrong_houses_sold_format.csv';
        $header = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $row1 = '1995-01-01,city of london,91449,E09000001,1sdfdsfdsfd7,,1';
        $content = implode(PHP_EOL, [ $header, $row1 ]);

        Storage::fake($fileName);

        $file = UploadedFile::fake()->createWithContent($fileName, $content);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'errors' => [
                '"Houses sold" column format must be a number'
            ]
        ]);
    }

    public function test_has_data_not_save_to_database() {
        $fileName = 'has_data_not_save_to_database.csv';
        $header = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $row1 = '1995-01-01,city of london,91449,E09000001,17,,1';
        $row2 = '2003-04-01,city of london,295174,E09000001,30,0.0,1';
        $row3 = '2003-05-01,city of london,284860,E09000001,38,0.0,1';
        $content = implode(PHP_EOL, [ $header, $row1, $row2, $row3 ]);

        Storage::fake($fileName);

        $file = UploadedFile::fake()->createWithContent($fileName, $content);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'avgPrice' => round((91449 + 295174 + 284860) / 3, 2),
                'totalHousesSold' => 85,
                'noOfCrimes' => 0,
                'avgPricePerYear' => [
                    '1995' => [ 'avg' => 91449 ],
                    '2003' => [ 'avg' => (295174 + 284860) / 2 ]
                ]
            ]
        ]);
        $this->assertDatabaseCount('data_uploads', 0);

    }

    public function test_has_data_save_to_database() {
        $fileName = 'has_data_save_to_database.csv';
        $header = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $row1 = '1995-01-01,city of london,91449,E09000001,17,,1';
        $row2 = '2003-04-01,city of london,295174,E09000001,30,0.0,1';
        $row3 = '2003-05-01,city of london,284860,E09000001,38,0.0,1';
        $content = implode(PHP_EOL, [ $header, $row1, $row2, $row3 ]);

        Storage::fake($fileName);

        $file = UploadedFile::fake()->createWithContent($fileName, $content);

        $response = $this->post('/upload', [
            'file' => $file,
            'saveToDB' => 'true'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'avgPrice' => round((91449 + 295174 + 284860) / 3, 2),
                'totalHousesSold' => 85,
                'noOfCrimes' => 0,
                'avgPricePerYear' => [
                    '1995' => [ 'avg' => 91449 ],
                    '2003' => [ 'avg' => (295174 + 284860) / 2 ]
                ]
            ]
        ]);
        $this->assertDatabaseCount('data_uploads', 3);

    }
}
