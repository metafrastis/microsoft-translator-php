<?php

namespace Metafrastis\MicrosoftTranslator;

class MicrosoftTranslator {

    public $queue = [];
    public $response;
    public $responses = [];

    public function translate($args = [], $opts = []) {
        $args['base'] = isset($args['base']) ? $args['base'] : 'https://api.cognitive.microsofttranslator.com';
        $args['version'] = isset($args['version']) ? $args['version'] : '3.0';
        $args['key'] = isset($args['key']) ? $args['key'] : null;
        $args['region'] = isset($args['region']) ? $args['region'] : null;
        $args['from'] = isset($args['from']) ? $args['from'] : null;
        $args['to'] = isset($args['to']) ? $args['to'] : null;
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        if (!$args['base']) {
            return false;
        }
        if (!$args['version']) {
            return false;
        }
        if (!$args['key']) {
            return false;
        }
        if (!$args['from']) {
            return false;
        }
        if (!$args['to']) {
            return false;
        }
        if (!$args['text']) {
            return false;
        }
        $url = $args['base'].'/translate?'.http_build_query(['api-version' => $args['version'], 'from' => $args['from']]);
        if (is_string($args['to'])) {
            if (strpos($args['to'], ',') !== false) {
                foreach (array_unique(explode(',', $args['to'])) as $to) {
                    $url .= '&to='.rawurlencode($to);
                }
            } else {
                $url .= '&to='.rawurlencode($args['to']);
            }
        } elseif (is_array($args['to'])) {
            foreach (array_unique($args['to']) as $to) {
                $url .= '&to='.rawurlencode($to);
            }
        }
        $headers = [
            'Content-type: application/json',
            'Ocp-Apim-Subscription-Key: '.$args['key'],
        ];
        if ($args['region']) {
            $headers[] = 'Ocp-Apim-Subscription-Region: '.$args['region'];
        }
        $params = json_encode([['Text' => $args['text']]]);
        if (is_array($args['text'])) {
            $texts = [];
            foreach ($args['text'] as $text) {
                $texts[] = ['Text' => $text];
            }
            $params = json_encode($texts);
        }
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if (!$queue) {
            $this->response = $response;
        }
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (empty($json[0]['translations'][0]['text'])) {
            return false;
        }
        if (count($json) === 1 && substr_count($url, '&to=') === 1) {
            return $json[0]['translations'][0]['text'];
        }
        if (count($json) === 1 && substr_count($url, '&to=') >= 2) {
            $translations = [];
            foreach ($json[0]['translations'] as $translation) {
                $translations[$translation['to']] = $translation['text'];
            }
            return $translations;
        }
        if (count($json) >= 2 && substr_count($url, '&to=') === 1) {
            $translations = [];
            foreach ($json as $item) {
                $translations[] = $item['translations'][0]['text'];
            }
            return $translations;
        }
        if (is_array($args['text']) && count($args['text']) >= 2 && substr_count($url, '&to=') >= 2) {
            $translations = [];
            foreach ($json as $index => $item) {
                foreach ($item['translations'] as $translation) {
                    $translations[$index][$translation['to']] = $translation['text'];
                }
            }
            return $translations;
        }
        return $json;
    }

    public function detect($args = [], $opts = []) {
        $args['base'] = isset($args['base']) ? $args['base'] : 'https://api.cognitive.microsofttranslator.com';
        $args['version'] = isset($args['version']) ? $args['version'] : '3.0';
        $args['key'] = isset($args['key']) ? $args['key'] : null;
        $args['region'] = isset($args['region']) ? $args['region'] : null;
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        if (!$args['base']) {
            return false;
        }
        if (!$args['version']) {
            return false;
        }
        if (!$args['key']) {
            return false;
        }
        if (!$args['text']) {
            return false;
        }
        $url = $args['base'].'/detect?'.http_build_query(['api-version' => $args['version']]);
        $headers = [
            'Content-type: application/json',
            'Ocp-Apim-Subscription-Key: '.$args['key'],
        ];
        if ($args['region']) {
            $headers[] = 'Ocp-Apim-Subscription-Region: '.$args['region'];
        }
        $params = json_encode([['Text' => $args['text']]]);
        if (is_array($args['text'])) {
            $texts = [];
            foreach ($args['text'] as $text) {
                $texts[] = ['Text' => $text];
            }
            $params = json_encode($texts);
        }
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if (!$queue) {
            $this->response = $response;
        }
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (empty($json[0]['language'])) {
            return false;
        }
        if (count($json) === 1) {
            return $json[0]['language'];
        }
        $detections = [];
        foreach ($json as $detection) {
            $detections[] = $detection['language'];
        }
        return $detections;
    }

    public function post($url, $headers = [], $params = [], $options = [], $queue = false) {
        $opts = [];
        $opts[CURLINFO_HEADER_OUT] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 5;
        $opts[CURLOPT_ENCODING] = '';
        $opts[CURLOPT_FOLLOWLOCATION] = false;
        $opts[CURLOPT_HEADER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = is_array($params) || is_object($params) ? http_build_query($params) : $params;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_TIMEOUT] = 10;
        $opts[CURLOPT_URL] = $url;
        foreach ($opts as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
        if ($queue) {
            $this->queue[] = ['options' => $options];
            return;
        }
        $follow = false;
        if ($options[CURLOPT_FOLLOWLOCATION]) {
            $follow = true;
            $options[CURLOPT_FOLLOWLOCATION] = false;
        }
        $errors = 2;
        $redirects = isset($options[CURLOPT_MAXREDIRS]) ? $options[CURLOPT_MAXREDIRS] : 5;
        while (true) {
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $body = curl_exec($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            if ($error || $errno) {
                if ($errors > 0) {
                    $errors--;
                    continue;
                }
            } elseif ($info['redirect_url'] && $follow) {
                if ($redirects > 0) {
                    $redirects--;
                    $options[CURLOPT_URL] = $info['redirect_url'];
                    continue;
                }
            }
            break;
        }
        return $response;
    }

    public function multi($args = []) {
        if (!$this->queue) {
            return [];
        }
        $mh = curl_multi_init();
        $chs = [];
        foreach ($this->queue as $key => $request) {
            $ch = curl_init();
            $chs[$key] = $ch;
            curl_setopt_array($ch, $request['options']);
            curl_multi_add_handle($mh, $ch);
        }
        $running = 1;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        $responses = [];
        foreach ($chs as $key => $ch) {
            curl_multi_remove_handle($mh, $ch);
            $body = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            $this->responses[$key] = $response;
            $options = $this->queue[$key]['options'];
            if (strpos($options[CURLOPT_URL], '/translate') !== false) {
                $json = json_decode($body, true);
                if (empty($json[0]['translations'][0]['text'])) {
                    $responses[$key] = false;
                    continue;
                }
                if (count($json) === 1 && substr_count($options[CURLOPT_URL], '&to=') === 1) {
                    $responses[$key] = $json[0]['translations'][0]['text'];
                    continue;
                }
                if (count($json) === 1 && substr_count($options[CURLOPT_URL], '&to=') >= 2) {
                    $translations = [];
                    foreach ($json[0]['translations'] as $translation) {
                        $translations[$translation['to']] = $translation['text'];
                    }
                    $responses[$key] = $translations;
                    continue;
                }
                if (count($json) >= 2 && substr_count($options[CURLOPT_URL], '&to=') === 1) {
                    $translations = [];
                    foreach ($json as $item) {
                        $translations[] = $item['translations'][0]['text'];
                    }
                    $responses[$key] = $translations;
                    continue;
                }
                if (substr_count($options[CURLOPT_POSTFIELDS], '"Text":"') >= 2 && substr_count($options[CURLOPT_URL], '&to=') >= 2) {
                    $translations = [];
                    foreach ($json as $index => $item) {
                        foreach ($item['translations'] as $translation) {
                            $translations[$index][$translation['to']] = $translation['text'];
                        }
                    }
                    $responses[$key] = $translations;
                    continue;
                }
                $responses[$key] = $json;
            } elseif (strpos($options[CURLOPT_URL], '/detect') !== false) {
                $json = json_decode($body, true);
                if (empty($json[0]['language'])) {
                    $responses[$key] = false;
                    continue;
                }
                if (count($json) === 1) {
                    $responses[$key] = $json[0]['language'];
                    continue;
                }
                $detections = [];
                foreach ($json as $detection) {
                    $detections[] = $detection['language'];
                }
                $responses[$key] = $detections;
            } else {
                $responses[$key] = $body;
            }
        }
        curl_multi_close($mh);
        $this->queue = [];
        return $responses;
    }

}
