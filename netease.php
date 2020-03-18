<?php
// 网易云 API 链接（最后不用加 /）
$localurl = "";
// 网易云音乐账号
$username = "";
// 网易云音乐密码
$password = "";

$gl = 1;

function getcurl($url, $cookies, $headid)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, $headid);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function get_rec_res($cookies)
{
    global $localurl;
    $url = $localurl . "/recommend/resource";
    return getcurl($url, $cookies, 0);
}

function get_song($cookies, $id)
{
    global $localurl;
    $url = $localurl . "/playlist/detail?id={$id}";
    return json_decode(getcurl($url, $cookies, 0), true);
}

function daka($cookies, $id)
{
    global $localurl;
    $url = $localurl . "/scrobble?id={$id}&time=90&timestamp=" . rand(1, 100000);
    return json_decode(getcurl($url, $cookies, 0), true);
}

function qdao($cookies)
{
    global $localurl;
    $url = $localurl . "/daily_signin";
    return json_decode(getcurl($url, $cookies, 0), true);
}

function login($username, $password)
{
    global $localurl;
    $url = $localurl . "/login/cellphone?phone={$username}&password={$password}";
    $data = getcurl($url, 0, 1);
    if (preg_match_all('/Set-Cookie:(.*);/iU', $data, $str) == 0) {
        die($data);
    }
    $cookies = $str[1][0] . ";" . $str[1][1] . ";" . $str[1][2] . ";";
    return $cookies;
}

function run($username, $password)
{
    global $localurl;
    $cookies = login($username, $password);
    $songslist = json_decode(get_rec_res($cookies), true);
    qdao($cookies);
    for ($k = 0; $k < 13; $k++) {
        $songlist = get_song($cookies, $songslist["recommend"][$k]["id"]);
        for ($j = 0; $j < count($songlist["privileges"]); $j++) {
            daka($cookies, $songlist["privileges"][$j]["id"]);
            if ($j == count($songlist["privileges"]) - 1 || $j == 300) {
                break 1;
            }
        }
        sleep(20);
    }
}

function main_handler($event, $context)
{
    global $gl;
    global $username;
    global $password;
    print "good";
    print " job ";
    print $gl;
    print "\n";
    $gl += 1;
    error_log("Errors");
    var_dump($event);
    var_dump($context);
    run($username, $password);
    return "Success";
}
