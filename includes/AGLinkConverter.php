<?php
include 'InformationApi.php';
/**
 * Created by PhpStorm.
 * User: erm
 * Date: 17-05-17
 * Time: 11:51
 */

class AGLinkConverter_Linkchanger extends AGLinkConverter_InformationApi
{

    private $c_id;
    public function __construct($c_id)
    {
        $this->c_id = $c_id;
    }



    public function makeSearchAbleWord($word)
    {
        return preg_replace('/\s+/', '', strtolower($word));
    }



    public function searchHotels($hotel)
    {

        global  $wpdb;
        $returnArray['hotel_id'] = '';
        $row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_hotel  WHERE hotel_name LIKE '%".$hotel."%'", ARRAY_A);
        if(!is_null($row)) {
            $returnArray['hotel_id'] = $row['hotel_id'];
        }

        return $returnArray;
    }



    /**
     * @param $landmark
     * @return mixed
     */
    public function searchLandmark($landmark)
    {


        global  $wpdb;
        $returnArray['city_id'] = '';
        $row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_landmark  WHERE landmark_name LIKE '%".$landmark."%'", ARRAY_A);
        if(!is_null($row)) {
            $returnArray['city_id'] = $row['city_id'];
        }

        return $returnArray;
    }



    /**
     * @param $city
     * @return mixed
     */
    public function searchCity($city)
    {


        global  $wpdb;
        $returnArray['city_id'] = '';
        $row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_city  WHERE city_name LIKE '%".$city."%'", ARRAY_A);
        if(!is_null($row)) {
            $returnArray['city_id'] = $row['city_id'];
        }
        return $returnArray;
    }


    /**
     * @param $area
     * @return mixed
     */
    public function searchArea($area)
    {

        $returnArray['area_id'] = '';
        global  $wpdb;
        $returnArray['area_id'] = '';
        $row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_area  WHERE area_name LIKE '%".$area."%'", ARRAY_A);
        if(!is_null($row)) {
            $returnArray['area_id'] = $row['area_id'];
        }
        return $returnArray;

    }

    /**
     * @param $string
     * @param null $one
     * @param null $two
     * @return string
     */
    public function stritr($string, $one = NULL, $two = NULL){

        if(  is_string( $one )  ){
            $two = strval( $two );
            $one = substr(  $one, 0, min( strlen($one), strlen($two) )  );
            $two = substr(  $two, 0, min( strlen($one), strlen($two) )  );
            $product = strtr(  $string, ( strtoupper($one) . strtolower($one) ), ( $two . $two )  );
            return $product;
        }
        else if(  is_array( $one )  ){
            $pos1 = 0;
            $product = $string;
            while(  count( $one ) > 0  ){
                $positions = array();
                foreach(  $one as $from => $to  ){
                    if(   (  $pos2 = stripos( $product, $from, $pos1 )  ) === FALSE   ){
                        unset(  $one[ $from ]  );
                    }
                    else{
                        $positions[ $from ] = $pos2;
                    }
                }
                if(  count( $one ) <= 0  )break;
                $winner = min( $positions );
                $key = array_search(  $winner, $positions  );
                $product = (   substr(  $product, 0, $winner  ) . $one[$key] . substr(  $product, ( $winner + strlen($key) )  )   );
                $pos1 = (  $winner + strlen( $one[$key] )  );
            }
            return $product;
        }
        else{
            return $string;
        }
    }/* endfunction stritr */


    /**
     * @param $url
     * @return bool
     */
    public function isHomepageLink($url)
    {

        $url_array = parse_url($url);
        return (isset($url_array['path']) && $url_array['path'] == '/' &&  strpos($url,'cid') !== false );
    }


    public function transformPopulairDest($keyword)
    {

        $api = $this->call_api(array('q'=>$keyword,'t'=>'populair_dest'));
        if(isset($api['url'])) {

        }
        return $keyword;
    }


    /**
     * Lazy match if this is an old hotel url link..
     * @param $url
     * @return bool
     */
    public function isOldHotelLink($url)
    {
        $url_array = parse_url($url);
        if(isset($url_array['path'])) {
            $split_url = explode('/',$url_array['path']);
            return count($split_url) == 5;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function transformOldHotelLinks($url)
    {
        $url_array = parse_url($url);
        if(isset($url_array['path'])) {
            $split_url = explode('/',$url_array['path']);
            $hotel_name = ucwords(str_replace('_',' ',$split_url[4]));
            $api = $this->call_api(array('q'=>$hotel_name,'t'=>'hotel'));
            if(!empty($api['hotel_id'])) {
                $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&hid='.$api['hotel_id']."&pcs=4";
            }
        }

        return $url;

    }



    /**
     * @param $url
     * @return string
     */
    public function transformHotelLinksNew($url)
    {
        $url_array = parse_url($url);
        if(isset($url_array['path'])) {
            $split_path = explode('/',$url_array['path']);
            $path_1 = (isset($split_path[1]) ? $split_path[1] : '');
            $split_country = explode('-',$path_1);
            $part_1 = (isset($split_country[0]) ? strlen($split_country[0]) : 0  );
            $part_2 = (isset($split_country[1]) ? strlen($split_country[1]) : 0);



            if($part_1 == 2 && $part_2 == 2 ) {
                $hotel_name = ucwords(str_replace('-',' ',$split_path[2]));

                $api = $this->call_api(array('q'=>$hotel_name,'t'=>'hotel'));
                if(!empty($api['hotel_id'])) {
                    $url = 'https://www.agoda.com/'.$path_1.'/partners/partnersearch.aspx?cid='.$this->c_id.'&pcs=4&hid='.$api['hotel_id'].'&wp=1';
                }
            } else {
                $hotel_name = ucwords(str_replace('-',' ',$split_path[1]));

                $api = $this->call_api(array('q'=>$hotel_name,'t'=>'hotel'));
                if(!empty($api['hotel_id'])) {
                    $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&hid='.$api['hotel_id'].'&pcs=4&wp=1';
                }

            }



        }
        return $url;
    }


    /**
     * @param $url
     * @return bool
     */
    public function isLandmarkLink($url)
    {
        return strpos($url,'/attractions/') !== false;
    }


    /**
     * @param $url
     * @return bool
     */
    public function isHotelLinkNew($url)
    {
        return strpos($url,'/hotel/') !== false;

    }


    /**
     * @param $url
     * @return bool
     */
    public function isAreaLink($url)
    {
        return strpos($url,'/maps/') !== false;
    }


    /**
     * @param $content
     * @return array
     */
    public function getCharactersBetween($content)
    {
        $s = explode("</a>",$content);
        $ahrefs = array();
        foreach ($s as $k ){
            if (strpos($k,"href" ) !==FALSE ){
                $ahrefs[] = preg_replace("/^.*href=\".*\">|\">.*/sm","",$k);
            }
        }
        return $ahrefs;
    }


    /**
     * @param $content
     * @return string
     */
    public function transformPopulairDestinations($content,$max_links_per_page=0)
    {


        $space_token = 'spacing';
        $populair_destinations = $this->getPopulairDestinations();
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content);
        $tags = $doc->getElementsByTagName('a');
        $link_anchors = [];
        foreach ($tags as $tag) {
            if(strpos($tag->getAttribute('href'),'cid=') === false|| strpos($tag->getAttribute('href'),'city') !== false  ) {
                $link_anchors[] = $tag->nodeValue;
                $new_a_txt = $tag->nodeValue."noreplace";
                $tag->nodeValue = $new_a_txt;
                $tag->setAttribute('a',$new_a_txt);
            }


        }
        $content = $doc->saveHTML();
        $link_anchors = array_unique($link_anchors);




        $counter = 0;
        $replacement_search = array();
        $replacement_replace = array();
        $to_be_replaced = $max_links_per_page;

        if(isset($populair_destinations['country'])) {
            foreach ($populair_destinations['country'] as $dests) {
                // kijk of spatie erin zit plaats dan token er tussen
                if (strpos($dests, $space_token)) {
                    $tmp_dest = str_replace($space_token, ' ', $dests);
                    $content = str_replace($tmp_dest, $dests, $content);
                }

            }
        }





        if(isset($populair_destinations['country'])) {
            foreach($populair_destinations['country'] as $dests) {
                $populair_destinations['url'][$counter] =  str_replace('XXXXXX',$this->c_id, $populair_destinations['url'][$counter]);
                $dests = preg_quote($dests, '/');
                if(preg_match('/\b'.$dests.'\b/',$content)  ) {
                    $replacement_search[$counter] = "/\b(" . $dests . ")(?=[^>]*(<|$))\b/";
                    $replacement_replace[$counter] = '<a href="' . $populair_destinations['url'][array_search($dests,$populair_destinations['country'])] . '&wp=2" target="_blank" rel="nofollow">' . ucwords($dests) . '</a>';
                    $counter ++;
                }
            }
        }





        for($i =0; $i <= $counter; $i++) {
            if(isset($replacement_search[$i]) && $replacement_replace[$i] && preg_match_all($replacement_search[$i],$content)) {
                $content = preg_replace($replacement_search[$i], $replacement_replace[$i], $content, $to_be_replaced, $count);
                $to_be_replaced = $to_be_replaced - $count;
            }

            if($to_be_replaced <= 0) {
                break;
            }

        }
        $content = str_replace($space_token,' ',$content);

        foreach($link_anchors as $anchors)  {
            $content = str_replace($anchors.'noreplace',$anchors,$content);
        }
        $content = str_replace('XXXXXX',$this->c_id,$content);

        return $content;
    }

    /**
     * @return array
     */
    public function getPopulairDestinations()
    {
        global $wpdb;
        $destinations = array();
        $countries =  $row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."agoda_countries", ARRAY_A);
        $counter = 0;
        foreach ($countries as $c) {

            $destinations['country'][$counter] = $c['dest'];
            $destinations['url'][$counter] = $c['url'];
            $counter++;
        }
        return $destinations;

    }

    /**
     * @param $url
     * @return bool
     */
    public function isCityLink($url)
    {
        return strpos($url,'/city/') !== false;
    }


    /**
     * Transform the Areay link
     * @param $url
     * @return string
     */
    public function transformArealink($url)
    {

        $explode_url = explode('/',$url);
        $area_name = (isset($explode_url[3]) ? ucwords(str_replace('-',' ',$explode_url[3])) : '');

        $api = $this->call_api(array('q'=>$area_name,'t'=>'area'));

        if(!empty($api['area_id'])) {
            $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&area='.$api['area_id']."&pcs=4&wp=1";
        }
        return $url;
    }



    /**
     * @param $url
     * @return string
     */
    public function transformLandmarkLink($url)
    {

        $explode_url = explode('/',$url);
        $landmark = (isset($explode_url[3]) ? preg_replace('/hotels-near-/','',$explode_url[3]) : '');
        $landmark = preg_replace('/-/',' ',ucwords($landmark));

        $api = $this->call_api(array('q'=>$landmark,'t'=>'landmark'));

        if(!empty($api['city_id'])) {
            $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&poi='.$api['city_id']."&pcs=4&wp=1";
        }

        return $url;

    }


    /**
     * @param $url
     * @return string
     */
    public function transformCityLink($url)
    {
        $explode_url = explode('/',$url);
        $city = (isset($explode_url[4]) ? $explode_url[4] : '');

        if(empty($city)) {
            return $url;
        }
        $city_extract = explode('.',$city);
        if(isset($city_extract[0])) {

            $has_language_tag = '';
            if($city == 'city') {
                $tmp_url = explode('-',$explode_url[5]);
                $has_language_tag = $explode_url[3];
                $city = $tmp_url[0];
            } else {
                $city_extract[0] = trim(substr( $city_extract[0],0,-3));
                $city = str_replace('-',' ',ucwords($city_extract[0]));
            }



            $api = $this->call_api(array('q'=>$city,'t'=>'city'));

            if(!empty($api['city_id'])) {
                if($has_language_tag != '') {
                    $url = 'https://www.agoda.com/'.$has_language_tag.'/partners/partnersearch.aspx?cid='.$this->c_id.'&city='.$api['city_id']."&pcs=4&wp=1";
                } else {
                    $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&city='.$api['city_id']."&pcs=4&wp=1";
                }

            }

        }

        return $url;
    }


    /**
     * Container changer factory
     * @param $url
     * @return string
     */
    public function contentChangerFactory($url)
    {
        $href_attributes = ' rel=nofollow';


        // return homepage link
        if($this->isHomepageLink($url)) {
            $url =  'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id."&pcs=4";
            $url = $url."\"{$href_attributes}";
            return $url;
            // city link
        } elseif($this->isCityLink($url)) {

            return $this->transformCityLink($url)."\"{$href_attributes}";
            // areay
        } elseif($this->isAreaLink($url)) {
            return $this->transformArealink($url)."\"{$href_attributes}";
            // landmark
        } elseif($this->isLandmarkLink($url)) {
            return $this->transformLandmarkLink($url)."\"{$href_attributes}";
        }elseif($this->isHotelLinkNew($url)) {

                return $this->transformHotelLinksNew($url)."\"{$href_attributes}";

            // show default link
        } elseif($this->isOldHotelLink($url)) {

                return $this->transformOldHotelLinks($url)."\"{$href_attributes}";


        } else {
            return $url;
        }

    }




}