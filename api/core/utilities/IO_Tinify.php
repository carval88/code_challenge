<?php
class IO_Tinify
{
    const API_KEY = 'KQycN3P3xK9QnmxDCVV0P5HkGCWnKnTw';
    const URL = 'https://api.tinify.com/shrink';

    public string $api_key;
    public string $original_image_url;
    public string $save_path;
    public string $tiny_image_url;
    public $tiny_image;
    public array $resize_methods = array('scale', 'fit', 'cover', 'thumb');

    protected $commands;

    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();
        if (method_exists($this, $function = '__construct' . $numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    public function __construct1(string $api_key)
    {
        $this->__set('api_key', $api_key);
    }

    public function __construct2(string $api_key, string $original_image_url)
    {
        $this->__set('api_key', $api_key);
        $this->__set('original_image_url', $original_image_url);
    }

    public function __construct3(string $api_key, string $original_image_url, string $save_path)
    {
        $this->__set('api_key', $api_key);
        $this->__set('original_image_url', $original_image_url);
        $this->__set('save_path', $save_path);
    }

    public function __set($name, $value)
    {
        if (property_exists(__CLASS__, $name))
            $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function compress()
    {
        try {
            $rs = $this->request(self::URL, file_get_contents($this->original_image_url));
            $this->__set('tiny_image_url', $rs->output->url);
            $this->__set('tiny_image', file_get_contents($this->tiny_image_url));
        } catch (Exception $e) {
            IOException::set($e->getMessage());
        }
    }

    public function resize(array $ops)
    {
        try {
            if (!$this->tiny_image_url) {
                $this->compress();
            }
            $ops =  json_encode(array('resize' => $ops));

            return $this->request($this->tiny_image_url, $ops);
        } catch (Exception $e) {
            IOException::set($e->getMessage());
        }
    }

    public function scale(array $arg)
    {
        $ops = array(
            'method'    => 'scale'
        );
        if (isset($arg['width'])) {
            $ops['width'] = $arg['width'];
        } else {
            if (isset($arg['height'])) {
                $ops['height'] = $arg['height'];
            } else {
                IOException::set('width or height are required.');
            }
        }
        $this->resize($ops);
    }
    public function fit(int $width, int $height)
    {
        $ops = array(
            'method'    => 'fit',
            'width'     => $width,
            'height'    => $height
        );

        $this->resize($ops);
    }
    public function cover(int $width, int $height)
    {
        $ops = array(
            'method'    => 'cover',
            'width'     => $width,
            'height'    => $height
        );

        $this->resize($ops);
    }
    public function thumb(int $width, int $height)
    {
        $ops = array(
            'method'    => 'thumb',
            'width'     => $width,
            'height'    => $height
        );

        $this->resize($ops);
    }

    public function save()
    {
        file_put_contents($this->save_path, $this->tiny_image);
    }

    protected function request($url, $data)
    {
        $ops = array(
            CURLOPT_URL => self::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_USERPWD => 'api:' . self::API_KEY,
            CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
            CURLOPT_POSTFIELDS => file_get_contents($this->original_image_url)
        );

        $ch = curl_init();
        curl_setopt_array($ch, $ops);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);

        return $response;
    }
}
