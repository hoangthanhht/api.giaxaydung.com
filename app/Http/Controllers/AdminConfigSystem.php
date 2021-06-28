<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminConfigSystem extends Controller
{
    public function configEnvForOneCoupleValue($variable, $value)
    {
        // $string = "This is an example string";
        // echo strtok($string, "men") . "<br />";
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        //$pos = strpos($str, 'Laravel');
        $arrgiaDaCo = explode("\r\n", $str);
        for ($key = 0; $key < count($arrgiaDaCo); $key++) {
            if (str_contains($arrgiaDaCo[$key], $variable)) {
                $arrgiaDaCo[$key] = "{$variable}={$value}";
                break;
            }
        }

        //$str = str_replace("{$variable}={$oldValue}", "{$variable}={$value}", $str);
        $str = implode("\r\n", $arrgiaDaCo);
        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
        Artisan::call('config:clear');
        return true;
    }

    public function changeEnvironment(Request $request)
    {

        try {
            $arrData = json_decode($request->data,true);//tham số thứ 2 là true thì sẽ lấy dạng mảng liên hợp, nếu không có hoặc bằng false thì nó sẽ lấy dạng obj
            //echo ($arrData[0]["MAIL_MAILER"]);
            foreach ($arrData as $key=>$vl) {
                $variable = array_keys($vl)[0];
                $value = $vl[$variable];
            $this->configEnvForOneCoupleValue($variable,$value);

            }
        } catch (Exception $exception) {
            DB::rollBack();
            $this->reportException($exception);

            $response = $this->renderException($request, $exception);

        }

    }


    public function offSystem() 
    {
        Artisan::call('down');
    }


    public function onSystem() 
    {
        Artisan::call('up');
    }

    protected function changeEnv($data = array())
    {
        if (count($data) > 0) {

            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');

            // Split string on every " " and write into array
            $env = preg_split('/\s+/', $env);

            // Loop through given data
            foreach ((array) $data as $key => $value) {

                // Loop through .env-data
                foreach ($env as $env_key => $env_value) {

                    // Turn the value into an array and stop after the first split
                    // So it's not possible to split e.g. the App-Key by accident
                    $entry = explode("=", $env_value, 2);

                    // Check, if new key fits the actual .env-key
                    if ($entry[0] == $key) {
                        // If yes, overwrite it with the new one
                        $env[$env_key] = $key . "=" . $value;
                    } else {
                        // If not, keep the old one
                        $env[$env_key] = $env_value;
                    }
                }
            }

            // Turn the array back to an String
            $env = implode("\n", $env);

            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);

            return true;
        } else {
            return false;
        }
    }
}
