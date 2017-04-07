<?php
/*-----------------------------------------------------------------+
| This script is for getting a World of Tanks clanwar battles info |
+------------------------------------------------------------------+
| Author  : Martin Jansky / justyand                               |
| Contact : justyand@gmail.com                                     |
+------------------------------------------------------------------+
| Basic configuration                                              |
|  Calling page : ?cl_id=<CLAN_ID>&cl_t=<CLAN_TAG>                 |
|  Optional     : ?cl_id=<CLAN_ID>&cl_t=<CLAN_TAG>&_type=json      |
|                                                                  |
|   ? <CLAN_ID>  : 500009877-D-L                                   |
|     <CLAN_TAG> : D-L                                             |
|     _type=json : exports as JSON data ( not implemented yet )    |
+------------------------------------------------------------------+
| Functions                                                        |
| showMapInfo ( $battleID )                                        |
|  ? $battleID : ID of battle stored in $cw_info                   |
|    used for export battle info table                             |
|                                                                  |
| showProvInfo ( $battleID )                                       |
|  ? $battleID : ID of province stroed in $cw_info                 |
|    used for export province info table                           |
|                                                                  |
| getInfo ( $address, $token, $jsonDecode, $json )                 |
|  ? $address    : address of calling page                         |
|    $token      : token used for accept script by WoT page        |
|    $jsonDecode : TRUE/FALSE - decode output with json parser     |
|    $json       : TRUE/FALSE - send json header                   |
|    used for calling page                                         |
|                                                                  |
| array_flatten ( $array, $preserve_keys = false )                 |
|  ? not coded by myself                                           |
|    used for flatten multidimensional array                       |
|                                                                  |
| writeText ( $data, $isLast )                                     |
|  ? $data   : data to be written                                  |
|    $isLast : TRUE/FALSE - is this last entry?                    |
|    used for fine-tunning graphic layout ( not required )         |
|                                                                  |
| parseHTMLInfo ( $page, $battleID )                               |
|  ? $page     : page to get HTML info about battle                |
|    $battleID : ID of battle used for get Enemy clan's name       |
|    used for getting detailed info about battle                   |
|      ( enemy, queue, ... )                                       |
+-----------------------------------------------------------------*/

// obtain php-fusion maincore file
require_once ( '../maincore.php' );

If (( !isset($_GET['cl_id'])) OR ( !isset($_GET['cl_t'])))
{ 
  // default configuration
  $clan = Array ( 'id' => '500009877-D-L',
                  'tag' => 'D-L'
                );
} else
{
  $clan = Array ( 'id' => $_GET['cl_id'],
                  'tag' => $_GET['cl_t']
                );
}

$clan['cl_id'] = str_replace( '-'.$clan['tag'], '', $clan['id'] );

// token of logged-in user from php-fusion
If ( !isset( $_GET['token'] ) )
{
  $token = '';
} else
{
  $token = $_GET['token'];
}

$timeoffset = timezone_offset_get( timezone_open( 'Europe/Prague' ), new DateTime() );

$settings = Array ( 'token'        => $token,
                    'adjust'       => ( date_offset_get(new DateTime) ) / ( $timeoffset ), // time offset due summer/winter time
                    'curl_timeout' => '20', // timeout used when CW turned off
                    'echoJSON'     => (( isset( $_GET['_type'] )) AND ( $_GET['_type'] == 'json' )) ? TRUE : FALSE,
                    'refferer'     => $_SERVER['REMOTE_ADDR'],
                    'map'          => 'http://cw1.worldoftanks.eu<PAGE>?type=dialog',
                    'wotcs'        => 'http://wotlabs.net/eu/clan/<CL_ID>',
                    'api_appid'    => 'secret_app_id_from_wg',
                    'address'      =>
                      Array ( 'http://cw1.worldoftanks.eu/clans/'.$clan['id'].'/battles/?type=table&offset=0&limit=1000&order_by=default&search=&echo=1&id=js-battles-table',
                              'http://cw1.worldoftanks.eu/clans/'.$clan['id'].'/provinces/?type=table&offset=0&limit=1000&order_by=name&search=&echo=1&id=js-provinces-table',
                              'http://cw1.worldoftanks.eu/clanwars/maps/provinces/regions/1/?ct=json',
                              'http://cw1.worldoftanks.eu/clanwars/maps/provinces/regions/2/?ct=json',
                              'http://cw1.worldoftanks.eu/clanwars/maps/provinces/regions/3/?ct=json'
                             )
                  );

If ( !isset( $_GET['lang'] ) )
{
  // define default language
  define( 'lang', 'en' );
} else
{
  define( 'lang', $_GET['lang'] );
}

$lang = Array ( 'cs' => Array (
                                'battles'            => 'Bitvy',
                                'province'           => 'Vlastněných provincií',
                                'days'               => 'Dnů',
                                'continue'           => 'Probíhá',
                                'ended'              => 'Ukončeno',
                                'waiting'            => 'Čeká',
                                'draw'               => 'remíza',
                                'looser'             => 'prohra',
                                'winner'             => 'výhra',
                                'queue'              => 'V pořadí',
                                'owner'              => 'Vlastník',
                                'enemy'              => 'Nepřítel',
                                'main_time'          => 'Hlavní čas',
                                'landing'            => 'Vylodění',
                                'for_province'       => 'Bitva o provincii',
                                'meeting_engagement' => 'Bitva o hranice',
                                'no_battles'         => 'Nejsou naplánované bitvy',
                                'no_province'        => 'Vlastněných provincií: 0',
                                'occupancy_time'     => 'Držíme dní',
                                'server'             => 'Server',
                                'n_europe'           => 'Severní Evropa',
                                'middle'             => 'Středomoří',
                                'w_africa'           => 'Západní Afrika',
                                'cw_stopped'         => 'CW pozastaveny',
                                'type'               => 'Typ',
                                'status'             => 'Stav',
                                'clans'              => 'klanů',
                                'check_login'        => 'Přihlašte se',
                                'competitors'        => 'Přihlášených klanů',
                                'enemies'            => 'Nepřátel',
                                // map types
                                'm_normal'           => 'Standardní',
                                'm_start'            => 'Vyloďovací',
                                'm_gold'             => 'GOLD'
                              ),
                'en' => Array (
                                'battles'            => 'Battles',
                                'province'           => 'Owned provinces',
                                'days'               => 'Days',
                                'continue'           => 'In charge',
                                'ended'              => 'Ended',
                                'waiting'            => 'Waiting',
                                'draw'               => 'draw',
                                'looser'             => 'lost',
                                'winner'             => 'won',
                                'queue'              => 'In queue',
                                'owner'              => 'Owner',
                                'enemy'              => 'Enemy',
                                'main_time'          => 'Prime time',
                                'landing'            => 'Landing',
                                'for_province'       => 'Battle for province',
                                'meeting_engagement' => 'Battle for borders',
                                'no_battles'         => 'No planned battles',
                                'no_province'        => 'Owned provinces: 0',
                                'occupancy_time'     => 'Holding',
                                'server'             => 'Server',
                                'n_europe'           => 'Northern Europe',
                                'middle'             => 'Mediterranean',
                                'w_africa'           => 'West Africa',
                                'cw_stopped'         => 'CW stopped',
                                'type'               => 'Type',
                                'status'             => 'Status',
                                'clans'              => 'clans',
                                'check_login'        => 'You must login',
                                'competitors'        => 'Clans applied',
                                'enemies'            => 'Attackers',
                                // map types
                                'm_normal'           => 'Standard',
                                'm_start'            => 'Landing',
                                'm_gold'             => 'GOLD'
                              )

              );
              
If (!(iUSER || iADMIN || iSUPERADMIN )) { die( '<center>'.$lang[lang]['check_login'].'</center>' ); }

$global_data = Array ();

$cw_info     = Array ( 'battles' => Array (),
                       'provinces' => Array ()
                      );

foreach( $settings['address'] as $_add )
{
  array_push( $global_data, getInfo( $_add, $settings['token'], true, true ));
};

$cw_info['battles']['count'] = ( isset($global_data[0]['request_data']['total_count'])) ? $global_data[0]['request_data']['total_count'] : '0';
$cw_info['provinces']['count'] = ( isset($global_data[1]['request_data']['total_count']) ) ? $global_data[1]['request_data']['total_count'] : '0';

foreach( $global_data[0]['request_data']['items'] as $num => $battle )
{
  $cw_info['battles'][$num] =
   Array ( 'map'           => $battle['arenas'][0],
           'time'          => $battle['time'],
           'type'          => $battle['type'],
           'type_id'       => -1,
           'started'       => $battle['started'],
           'id'            => $battle['provinces'][0]['id'],
           'province_name' => $battle['provinces'][0]['name'],
           'status'        => $lang[lang]['waiting'],
           'statusID'      => '0'
          );
          
   switch ( $battle['type'] )
   {
    case 'landing':
     $cw_info['battles'][$num]['type_id'] = 0;
     break;
    case 'for_province':
     $cw_info['battles'][$num]['type_id'] = 1;
     break;
    case 'meeting_engagement':
     $cw_info['battles'][$num]['type_id'] = 2;
     break;
    default:
     $cw_info['battles'][$num]['type_id'] = -1;
   }

   // search battle link
   for( $i = 2; $i < count($global_data); $i++)
   {
     $help = $cw_info['battles'][$num]['id'];
     If ( array_key_exists( $help, $global_data[$i]['provinces']) <> FALSE )
     {
       $helpProvince = $global_data[$i]['provinces'][$help];

       $helpArray = Array ( 'landing_url'    => $helpProvince['landing_url'],
                            'owner'          => $helpProvince['clan'],
                            'ownerID'        => $helpProvince['clanId'],
                            'owner_duration' => $helpProvince['ownering_duration'],
                            'prime_time'     => $helpProvince['prime_time'],
                            'time_lag'       => $helpProvince['landing_tournament_lag'],
                            'p_started'      => $helpProvince['started'],
                            'gold'           => $helpProvince['revenue'],
                            'max_tier'       => $helpProvince['heavy_tank_max_level'],
                            'server'         => $helpProvince['periphery'],
                            'competitors'    => $helpProvince['landing_competitors_count'],
                            'combats'        => $helpProvince['combats'], // battles on normal provinces
                            'region'         => $i - 1
                          ); // region : 1 - north europe / 2 - Mediterranean / 3 - West Africa
       // get owner tag
       $helpInfo = getInfo ( 'http://api.worldoftanks.eu/wot/clan/info/?language=cs&clan_id='.$helpArray['ownerID'].'&application_id='.$settings['api_appid'],'', true, true );
       $helpInfo = Array ( 'ownerTAG' => $helpInfo['data'][$helpArray['ownerID']]['abbreviation'] );
       $helpArray = array_merge( $helpArray, $helpInfo );

       // get enemy from 'combats'
       foreach( $helpArray['combats'] as &$combats )
       {
         if ( $cw_info['battles'][$num]['time'] == $combats['combatants'][$clan['cl_id']]['at'] ) 
         { 
           $v = $combats['combatants'];
           foreach( $v as $key => $value )
           {
             if ( $key != $clan['cl_id'] ) { $helpArray['combat_enemy'] = $key; break 2; }
           }
         };

       }

       $cw_info['battles'][$num] = array_merge( $helpArray, $cw_info['battles'][$num] );

     };
   };
};

$cw_info['province']['count'] = $global_data[1]['request_data']['total_count'];

foreach( $global_data[1]['request_data']['items'] as $num => $province )
{
  $cw_info['province'][$num] =
   Array ( 'map'            => $province['arena_name'],
           'time'           => $province['prime_time'],
           'type'           => $province['type'],
           'id'             => strtoupper($province['id']),
           'name'           => $province['name'],
           'gold'           => $province['revenue'],
           'owner_duration' => $province['occupancy_time'],
           'capital'        => $province['capital']
         );

  for( $i = 2; $i < count($global_data); $i++)
  {
    $help = $cw_info['province'][$num]['id'];
    If ( array_key_exists( $help, $global_data[$i]['provinces']) <> FALSE )
    {
      $helpProvince = $global_data[$i]['provinces'][$help];

      $helpArray = Array (
                           'server'      => $helpProvince['periphery'],
                           'competitors' => $helpProvince['landing_competitors_count'],
                           'battles'     => count($helpProvince['combats'])
                         );
    }
  }
  $cw_info['province'][$num] = array_merge( $helpArray, $cw_info['province'][$num] );
}

/*------------------------------------+
|                                     |
|        writing Battle data          |
|                                     |
+------------------------------------*/

If ( $settings['echoJSON'] == FALSE )
{

writeButton( 'CW_data_c_', $lang[lang]['battles'], $cw_info['battles']['count'] );

if ( $cw_info['battles']['count'] == 0 )
 {
   echo '<div style="border: 1px solid #333; background-color: #111; padding: 5px;margin-bottom: 5px; position: relative">';
   echo writeText($lang[lang]['no_battles'], TRUE);
   echo '</div>';
 } else
 {
   foreach( $cw_info['battles'] as $key => $bat )
    {
       If ( is_int($key) ) { 
         If ( $bat['type_id'] == 0 ) { parseHTMLInfo( $bat['landing_url'], $key ); } else { $cw_info['battles'][$key]['status'] = $lang[lang]['waiting']; }
         showMapInfo( $key ); }

    }
 }
writeButton( '', '', '', FALSE );

/*------------------------------------+
|                                     |
|       writing Province data         |
|                                     |
+------------------------------------*/
switch ( $cw_info['province']['count'] )
{
  case '0' :
      echo writeButton( 'PROV_data_c_', $lang[lang]['province'], '0' );
      break;
  default:
      echo writeButton( 'PROV_data_c_', $lang[lang]['province'], $cw_info['province']['count'] );
}

switch ( $cw_info['province']['count'] )
{
  case '0' :
   echo '<div style="border: 1px solid #333; background-color: #111; padding: 5px;margin-bottom: 5px">';
   echo writeText($lang[lang]['no_province'], TRUE);
   echo '</div>';
   break;
  default:
   foreach($cw_info['province'] as $key => $province )
   {
     If ( is_int($key) ) { showProvInfo( $key ); };
   }
}

} else {
  foreach( $cw_info['battles'] as $key => $bat )
    {
       If ( is_int($key) ) { parseHTMLInfo( $bat['landing_url'], $key ); }

    }
    
   echo json_encode($cw_info);
}

/*-------------------------------------------------------+
|                                                        |
|                 FUNCTION writeButton                   |
|                                                        |
+-------------------------------------------------------*/

function writeButton( $idClass, $text, $data ,$start = TRUE )
{ 
  global $lang, $clan, $cw_info;
  If ( $start )
  {
    echo '<div class="scapmain" style="border: 0; background-image: url(\'\');padding-top: 8px;"><span style="cursor: pointer" onClick="action( \''.$idClass.$clan['tag'].'\', 400)">'.$text.' <small>( '.$data.' )</small></span></div>';
    echo '<div id="'.$idClass.$clan['tag'].'">';
  } else
  {
    echo '</div>';
  }
}

/*-------------------------------------------------------+
|                                                        |
|                 FUNCTION showMapInfo                   |
|                                                        |
+-------------------------------------------------------*/

function showMapInfo( $battleID )
{ global $lang, $cw_info, $settings, $clan;



$help = $cw_info['battles'][$battleID];

 // get enemy tag
 $helpInfo = getInfo ( 'http://api.worldoftanks.eu/wot/clan/info/?language=cs&clan_id='.$help['enemy_id'].'&application_id='.$settings['api_appid'],'', true, true );
 $helpInfo = Array ( 'enemyTAG' => $helpInfo['data'][$help['enemy_id']]['abbreviation'] );
 $help = array_merge( $helpInfo, $help );

 $prim_time = ( $help['time'] <> 0 ) ?  gmdate('H:i', $help['time'] + ( 60* 60 * $settings['adjust']))  : '--:--';
 $timestamp = getdate();
 $d_l = mktime( 0, 0, 0, $timestamp['mon'], $timestamp['mday'], $timestamp['year']);
 $d_h = mktime( 23, 59, 59, $timestamp['mon'], $timestamp['mday'], $timestamp['year']);

 $bat_time = (( $bat['started'] != TRUE ) AND ( $help['prime_time'] > $d_h ) AND ( $cw_info['battles'][$battleID]['statusID'] == 2 ))
             ? gmdate('d. m. Y', $help['prime_time'] + ( 60 * 60 * $settings['adjust'] ) + ( 60 * $help['time_lag'] ))
             : gmdate('H:i', $help['prime_time'] + ( 60 * 60 * $settings['adjust'] ) + ( 60 * $help['time_lag'] ));


 echo '<div style="position: relative; overflow: hidden; border: 1px solid #333; background-color: #111; padding: 5px;margin-bottom: 5px">';
 echo '<div style="position: absolute; right: 0px; z-index: 1; background-image: url(\'ftp_upload/fade.png\'); background-size: 100%; background-repeat: repeat-y; width: 40%; height: 100%">&nbsp</div>';

 // write link with province, map and time
 echo '<center><a target="_blank" href="http://worldoftanks.eu/clanwars/maps/globalmap/?province='.$help['id'].'">';
 echo $help['province_name'].'<br />( '.$help['map'].' / '.$prim_time;
 echo ' )</a></center><br />';

 echo writeText('enemy: '.$help['combat_enemy'], FALSE);
 // write Enemy if possible
 If ( $help['statusID'] == 1 )
  {
    If ( strlen($help['enemy_id']) > 0 )
    {
      echo writeText($lang[lang]['enemy'].': <a target="_blank" href="http://worldoftanks.eu/community/clans/'.$help['enemy_id'].'"><span style="z-index: 0">'.$help['enemy'].'</span></a><br /><center><div class="plus_info"><div style="display: none">'.$help['enemyTAG'].'</div><span style="display: none">'.$help['enemy_id'].'</span></div>', FALSE);
    } else
    {
      echo writeText($lang[lang]['enemy'].': <span style="z-index: 0">'.$help['enemy'].'</span>', FALSE);
    }
  }

 // write owner
 echo writeText($lang[lang]['owner'].': <a target="_blank" href="http://worldoftanks.eu/community/clans/'.$help['ownerID'].'"><span style="z-index: 0">'.$help['owner'].'</span></a>
               <br /><center><small><span style="color: #aaa">( '.$lang[lang]['days'].': '.$help['owner_duration'].' )</span></small></center><div class="plus_info"><div style="display: none">'.$help['ownerTAG'].'</div><span style="display: none">'.$help['ownerID'].'</span></div>', FALSE);

 // write server
 echo writeText($lang[lang]['server'].': <span style="color: #e00">'.$help['server'].'</span>', FALSE);

 echo writeText($lang[lang]['main_time'].': <span style="color: #aaa">'.$bat_time.'</span>', FALSE);

  switch ( $help['type_id'] )
  {
    case 0:
     $typ = $lang[lang]['landing'];
     if($help['competitors'] > 0) { $typ .= ' <small>( '.$help['competitors'].' '.$lang[lang]['clans'].' )</small>';};
     break;
    case 1:
     $typ = $lang[lang]['for_province'];
     break;
    case 2:
     $typ = $lang[lang]['meeting_engagement'];
     break;
  }

  echo writeText($lang[lang]['type'].': <span style="color: #aaa">'.$typ.'</span>', FALSE);

  echo writeText($lang[lang]['status'].': <span style="color: #aaa">'.$help['status'].'</span>', TRUE);

 echo '</div>';
}

/*-------------------------------------------------------+
|                                                        |
|                 FUNCTION showProvInfo                  |
|                                                        |
+-------------------------------------------------------*/

function showProvInfo( $battleID )
{ global $settings, $lang, $cw_info, $clan;

  $help = $cw_info['province'][$battleID];

 echo '<div style="border: 1px solid #333; background-color: #111; padding: 5px;margin-bottom: 5px;margin-top: 5px">';
 echo '<center><a target="_blank" href="http://worldoftanks.eu/clanwars/maps/globalmap/?province='.$help['id'].'">'.$help['name'].'<br />( '.$help['map'].' )</a></center><br />';

 echo writeText($lang[lang]['occupancy_time'].': '.$help['owner_duration']);
 echo writeText($lang[lang]['server'].': <span style="color: #e00">'.$help['server'].'</span>');
 if ( $help['type'] == 'start' )
  {
    echo writeText($lang[lang]['competitors'].': <span style="color: #e00">'.$help['competitors'].'</span>');
  } else
  {
    echo writeText($lang[lang]['enemies'].': <span style="color: #e00">'.$help['battles'].'</span>');
  }
 
 switch( $help['type'] ){
 case 'normal': $help['m_type'] = $lang[lang]['m_normal']; break;
 case 'start' : $help['m_type'] = $lang[lang]['m_start']; break;
 case 'gold'  : $help['m_type'] = $lang[lang]['m_gold']; break;
 default: $help['m_type'] = 'unknown'; break;
 }
 echo writeText($lang[lang]['type'].': <span style="color: #275FBE">'.$help['m_type'].'</span>');

 echo writeText('<span style="color: #eee">'.$help['gold'].'<img style="position: relative; top: 4px;" src="ftp_upload/gold.png" /></span>', TRUE);

 echo '</div>';
} // provincie

/*-------------------------------------------------------+
|                                                        |
|                   FUNCTION getInfo                     |
|                                                        |
+-------------------------------------------------------*/

function getInfo( $page, $appid, $jsonDecode, $json )
{
  global $settings, $lang;

  $pg = curl_init();

  curl_setopt( $pg, CURLOPT_URL, $page);
  curl_setopt( $pg, CURLOPT_TIMEOUT, $settings['curl_timeout']);
  curl_setopt( $pg, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt( $pg, CURLOPT_FRESH_CONNECT, 1);
  curl_setopt( $pg, CURLOPT_COOKIE, 'hllang='.lang);
  curl_setopt($pg, CURLOPT_HTTPHEADER,
              array(
                     'Connection: keep-alive',
                     'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                     'User-Agent: PHP_SELF',
                     'Referer: http://worldoftanks.eu/clanwars/maps/globalmap/',
                     'Accept-Encoding: html',
                     'Accept-Language: cs,en;q=0.8')
                   );

  $data = curl_exec( $pg );

  curl_close( $pg );

  if ( $jsonDecode )
  {
    $data = json_decode( $data, true);
  }

  If ( ( count($data) == 0 ) && ( strpos( $page, 'api' ) == FALSE ) )
  {
    echo '<div style="border: 1px solid #333; background-color: #111; padding: 5px;margin-bottom: 5px">';
    echo writeText('<center style="color: #933">'.$lang[lang]['cw_stopped'].'</center>', TRUE);
    echo '</div>';
    exit();
  } else { $error = 0; }

  return $data;

}

/*-------------------------------------------------------+
|                                                        |
|                FUNCTION array_flatten                  |
|                                                        |
+-------------------------------------------------------*/

function array_flatten ( $array, $preserve_keys = false )
{
    if (!$preserve_keys) {
        // ensure keys are numeric values to avoid overwritting when array_merge gets called
        $array = array_values($array);
    }

    $flattened_array = array();
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $flattened_array = array_merge($flattened_array, call_user_func(__FUNCTION__, $v, $preserve_keys));
        } elseif ($preserve_keys) {
            $flattened_array[$k] = $v;
        } else {
            $flattened_array[] = $v;
        }
    }
    return $flattened_array;
}

/*-------------------------------------------------------+
|                                                        |
|                  FUNCTION writeText                    |
|                                                        |
+-------------------------------------------------------*/

function writeText( $data, $isLast = False )
{
  $s_border = ' border-bottom: 1px solid #333';

  $border = ( !$isLast ) ? $s_border : '';

  $return = '<div style="padding: 5px 0px 5px 5px;'.$border.'">'.$data.'</div>';
  return $return;
}

/*-------------------------------------------------------+
|                                                        |
|                FUNCTION parseHTMLInfo                  |
|                                                        |
+-------------------------------------------------------*/

function parseHTMLInfo ( $page, $battleID )
{
  global $global_info, $settings, $clan, $cw_info, $lang;

  $html = str_replace('<PAGE>', $page, $settings['map']);

  $html = getInfo( $html, $settings['token'], false, false );

  preg_match_all('/<tr class="tournament-content-table"(.*?)<\/tr>/s',$html,$enemy); // getting enemy
  preg_match_all('/<table class="t-table t-landing-clan-names"(.*?)<\/table>/s',$html,$queue); // getting info about queue
  
  foreach( $queue[0] as $q )
  {
    preg_match_all('/<div class="b-ellipsis"(.*?)<\/div>/s', $q, $q1);
    foreach( $q1[0] as $q11 )
    {
      If ( strpos( $q11, $clan['tag']) !== FALSE )
      {
        $cw_info['battles'][$battleID]['status'] = $lang[lang]['waiting'];
        $cw_info['battles'][$battleID]['statusID'] = 2;
        break 2;
      };
    }
  }

  If ( strlen($cw_info['battles'][$battleID]['status'] ) == 0 )
  {
  foreach( $enemy[0] as $tr )
  {
    if ( strpos($tr, $clan['tag'] ) !== FALSE )
    {
      preg_match_all('/<td(.*?)<\/td>/s',$tr,$td);
      foreach ($td[0] as $t_clan)
      {
        If (( strpos($t_clan, $clan['tag']) === FALSE ) AND ( strpos($t_clan, 'time' ) === FALSE ) AND ( !$t_enemy ))
        {
          If ( strpos($t_clan, '---') != FALSE ) { $cw_info['battles'][$battleID]['enemy'] = '---'; } else
          {
          $t_enemy = strpos($t_clan, 'class="b-ellipsis"');
          $t_enemy1 = strpos($t_clan, '<', $t_enemy +1 );
          $t_enemy = substr($t_clan, $t_enemy + 19, $t_enemy1 - ($t_enemy +19 ));
          $cw_info['battles'][$battleID]['enemy'] = ( strlen($t_enemy) > 0 ) ? $t_enemy : "Unknown" ;}
          
          If ( strlen($t_enemy) > 0 )
          {
            $t_enemy = rawurlencode( $t_enemy );
            $html = 'http://api.worldoftanks.eu/wot/clan/list/?application_id='.$settings['api_appid'].'&language='.lang.'&search='.$t_enemy;
            $html = getInfo( $html, $settings['api_appid'], true, true );

            $cw_info['battles'][$battleID]['enemy_id'] = $html['data'][0]['clan_id'];
            $cw_info['battles'][$battleID]['enemy_id_wotcs'] = str_replace( '<CL_ID>', '['.$html['data'][0]['abbreviation'].']', $settings['wotcs']);
          }
        }
        If ((strpos($t_clan, $clan['tag']) !== FALSE ) AND (( strpos($t_clan, 'draw') > 0 ) OR ( strpos($t_clan, 'looser') > 0 )))
        {
          $cw_info['battles'][$battleID]['status'] = $lang[lang]['ended'];
          if ( strpos($t_clan, 'looser') > 0 ) {$cw_info['battles'][$battleID]['status'] .= '<small> ( '.$lang[lang]['looser']. ' )</small>'; } else
          if ( strpos($t_clan, 'draw') > 0 ) { $cw_info['battles'][$battleID]['status'] .= '<small> ( '.$lang[lang]['draw']. ' )</small>'; };
          $cw_info['battles'][$battleID]['statusID'] = 0;
          break 2;
        }
         else
        {
          if (( strpos($t_clan, $clan['tag']) !== FALSE ) AND ( strpos($t_clan, 'class=""') >= 0 ))
          {
          
            $cw_info['battles'][$battleID]['status'] = $lang[lang]['continue'];
            $cw_info['battles'][$battleID]['statusID'] = 1;

          } else
          if (( strpos($t_clan, $clan['tag']) !== FALSE ) AND ( strpos($t_clan, 'winner') > 0 ))
           {
              $cw_info['battles'][$battleID]['status'] = $lang[lang]['ended'].'<small> ( '.$lang[lang]['winner'].' )</small>';
              $cw_info['battles'][$battleID]['statusID'] = 0;
              break 2;
           } else
           {
              $cw_info['battles'][$battleID]['status'] = $lang[lang]['continue'];
              $cw_info['battles'][$battleID]['statusID'] = 1;
           }
        };
      };
    };
  };
  };

};

?>