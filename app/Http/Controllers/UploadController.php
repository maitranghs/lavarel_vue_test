<?php

namespace App\Http\Controllers;

use App\Models\DataUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DateTime;

class UploadController extends Controller
{
    private static $csv = 'csv';
    private static $year2011 = '2011';
    private static $london = 'london';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $errors = [];
        // Get the content from the request
        $saveToDB = $request->saveToDB === 'true';

        // File path
        $file = $request->file('file');
        $path = $file->getRealPath();

        // Validate file type
        $fileName = $file->getClientOriginalName();
        if(isset($fileName) && !str_contains($fileName, self::$csv)) {
            $errors[] = 'Please input a CSV file';
            return response()->json([
                'errors'=> $errors
            ]);
        }
        $data = array_map('str_getcsv', file($path));

        // Init response data
        $errors = $this->validateContentFile($data);
        $summary = (object)[
            'avgPrice'=> 0,
            'totalHousesSold'=> 0,
            'noOfCrimes'=> 0,
            'avgPricePerYear'=> new \stdClass()
        ];

        if(count($errors) > 0) {
            return response()->json([
                'errors'=> $errors
            ]);
        }

        // process to calculate the summarised data while checking each row
        $processedFirstRow = false;
        $dataRows = [];
        foreach($data as $line) {
            if(!$processedFirstRow) {
                $processedFirstRow = true;
                continue;
            }

            // sum of the average of all prices sold
            $summary->avgPrice += intval($line[2]);

            // count of all houses sold
            $summary->totalHousesSold += intval($line[4]);

            // number of crimes in 2011
            $year = substr($line[0], 0, 4);
            if($year === self::$year2011) {
                $summary->noOfCrimes += floatval($line[5]);
            }

            // count of the average price per year in the London area
            if (str_contains($line[1], self::$london)) {
                if(property_exists($summary->avgPricePerYear, $year)) {
                    $summary->avgPricePerYear->$year->cnt += 1;
                    $summary->avgPricePerYear->$year->value += intval($line[2]);
                } else {
                    $summary->avgPricePerYear->$year = (object)[
                        'cnt' => 1,
                        'value' => intval($line[2]),
                        'avg' => 0
                    ];
                }
            }

            // prepair data to insert into database
            if($saveToDB) {
                $dataRows[] = [
                    'date' => $line[0],
                    'area' => $line[1],
                    'average_price' => intval($line[2]),
                    'code' => $line[3],
                    'houses_sold' => intval($line[4]),
                    'no_of_crimes' => floatval($line[5]),
                    'borough_flag' => $line[6]
                ];
            }
        }

        if($saveToDB) {
            DataUpload::insert($dataRows);
        }
        
        // Process for showing
        $summary->avgPrice = round($summary->avgPrice / (count($data) - 1), 2);
        foreach($summary->avgPricePerYear as $year) {
            $year->avg = round($year->value / $year->cnt, 2);
        }

        return response()->json([
            'data'=> $summary
        ]);
    }

    /**
     * Private function validate the content of the file
     * @param  array $data
     * @return array $errors
     */
    private function validateContentFile(array $data) {

        $errors = [];

        // Validate file empty
        if (count($data) <= 1) {
            $errors[] = 'There is no data';
            return $errors;
        }
        // Validate the headers
        $fix_header_fields = 'date,area,average_price,code,houses_sold,no_of_crimes,borough_flag';
        $input_header_fields = implode(',', $data[0]);
        if (strcmp($fix_header_fields, $input_header_fields) != 0) {
            $errors[] = 'Header fields are not matched.';
        }

        // Validate the details
        $processedFirstRow = false;
        foreach($data as $line) {
            if(!$processedFirstRow) {
                $processedFirstRow = true;
                continue;
            }
            if (count($line) != 7) {
                $errors[] = 'The number of columns of a row are not equal 7';
                break;
            }
            if(!$this->validateDate($line[0])) {
                $errors[] = '"Date" column format is wrong, YYYY-MM-DD';
                break;
            }
            if(!is_numeric($line[5]) && $line[5] !== '') {
                $errors[] = '"Number of crimes" column format must be a number';
                break;
            }
            if(!is_numeric($line[4]) && $line[4] !== ''){
                $errors[] = '"Houses sold" column format must be a number';
                break;
            }
        }

        return $errors;
    }

    /**
     * Private function validate date format
     * @param  string $date
     * @return boolean
     */
    private function validateDate(string $date, string $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DataUpload  $dataUpload
     * @return \Illuminate\Http\Response
     */
    public function show(DataUpload $dataUpload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DataUpload  $dataUpload
     * @return \Illuminate\Http\Response
     */
    public function edit(DataUpload $dataUpload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DataUpload  $dataUpload
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DataUpload $dataUpload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DataUpload  $dataUpload
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataUpload $dataUpload)
    {
        //
    }
}
