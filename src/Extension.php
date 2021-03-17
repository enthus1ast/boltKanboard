<?php

namespace enthus1ast\bolteval;

// use Bolt\Extension\BaseExtension;
// use Bolt\Extension\SimpleExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Twig_Filter_Method;



class Extension extends AbstractExtension
{
    public function class() {}

    public function composerPackage() {}

    public function config() {}

    public function hasConfigFilenames() {}

    public function injectObjects() {}

    public function getName(): string {
      return "bolt eval curl";
    }

    public function phpEval($code): string {
      return eval($code);
    }

    public function createTask($user, $titleRaw, $descriptionRaw, $PROJECT_ID, $KANBOARD_API, $BASIC_AUTH) {
      $res = "";

      $title = json_encode($titleRaw);
      $description = json_encode($descriptionRaw);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $KANBOARD_API);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_USERPWD, $BASIC_AUTH);

      $BODY = <<<EOD
      {
        "jsonrpc": "2.0",
        "method": "createTask",
        "id": 1176509098,
        "params": {
          "owner_id": 0,
          "creator_id": 0,
          "date_due": "",
          "description": $description,
          "category_id": 0,
          "score": 0,
          "title": $title,
          "project_id": $PROJECT_ID,
          "color_id": "#33aabb",
          "column_id": 0,
          "recurrence_status": 0,
          "recurrence_trigger": 0,
          "recurrence_factor": 0,
          "recurrence_timeframe": 0,
          "recurrence_basedate": 0
        }
      }
EOD;


      curl_setopt($ch, CURLOPT_POSTFIELDS, $BODY);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $server_output = curl_exec($ch);

      if (!curl_errno($ch)) {
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
          case 200:  # OK
            curl_close ($ch);
            $server_output_obj = json_decode($server_output);
            if(array_key_exists("error", $server_output_obj)) {
              var_dump($server_output_obj);
              return false;
            } else {

              return true;
            }
            return true;
          default:
            echo 'Unexpected HTTP code: ', $http_code, "\n";
            curl_close ($ch);
            return false;
        }
      }

      return false;
    }

    public function createKanboardTask($user, $title, $body, $PROJECT_ID, $KANBOARD_API, $BASIC_AUTH) {
      $this->createTask($user, $title, $body, $PROJECT_ID, $KANBOARD_API, $BASIC_AUTH);
    }

    /**
     * Register Twig functions.
     */
    public function getFunctions(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];
        return [
            new TwigFunction('phpEval', [$this, 'phpEval'], $safe),
            new TwigFunction('createKanboardTask', [$this, 'createKanboardTask'], $safe),
            new TwigFunction('json_decode', [$this, 'jsonDecode'], $safe),
        ];
    }

    public function jsonDecode($str) {
        return json_decode($str);
    }

}

?>