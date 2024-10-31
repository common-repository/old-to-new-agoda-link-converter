<?php
/**
 * Created by PhpStorm.
 * User: erm
 * Date: 17-05-17
 * Time: 11:51
 */

class AgSqlInsert
{

    private $max_to_fetch = 10000;

    /**
     *
     */
    public function fetchPopulairDestinations()
    {
        global $wpdb;
        $pop_destinations_path = plugins_url( 'popular_destinations.csv', dirname( __FILE__ ).'/includes/' );
        $row = 1;
        if (($handle = fopen($pop_destinations_path, "r")) !== FALSE) {
            $counter = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $wpdb->query("INSERT INTO ".$wpdb->prefix ."agoda_countries (dest,url) VALUES('".strip_tags(utf8_decode(esc_sql($data[0])))."','".$data[1]."')");
                $counter ++;
                if($counter == 10000) {
                    break;
                }

            }
            fclose($handle);
        }
    }



    public function fetchHotels()
    {
        global $wpdb;
        $pop_destinations_path = plugins_url( 'hotels.csv', dirname( __FILE__ ).'/includes/' );
        $row = 1;
        if (($handle = fopen($pop_destinations_path, "r")) !== FALSE) {
            $counter = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $wpdb->query("INSERT INTO ".$wpdb->prefix ."agoda_hotel (hotel_id,hotel_name) VALUES('".esc_sql($data[0])."','".esc_sql($data[1])."')");
                $counter ++;
                if($counter == $this->max_to_fetch) {
                    break;
                }

            }
            fclose($handle);
        }
    }




    public function fetchCity()
    {
        global $wpdb;
        $pop_destinations_path = plugins_url( 'city_ids.csv', dirname( __FILE__ ).'/includes/' );
        $row = 1;
        if (($handle = fopen($pop_destinations_path, "r")) !== FALSE) {
            $counter = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $wpdb->query("INSERT INTO ".$wpdb->prefix ."agoda_city (city_id,city_name) VALUES('".esc_sql($data[0])."','".esc_sql($data[1])."')");
                $counter ++;
                if($counter == $this->max_to_fetch) {
                    break;
                }

            }
            fclose($handle);
        }
    }


    public function fetchArea()
    {
        global $wpdb;
        $pop_destinations_path = plugins_url( 'area_ids.csv', dirname( __FILE__ ).'/includes/' );
        $row = 1;
        if (($handle = fopen($pop_destinations_path, "r")) !== FALSE) {
            $counter = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $wpdb->query("INSERT INTO ".$wpdb->prefix ."agoda_area (area_id,city_id,area_name) VALUES('".esc_sql($data[0])."','".esc_sql($data[1])."','".esc_sql($data[2])."')");
                $counter ++;
                if($counter == $this->max_to_fetch) {
                    break;
                }
            }
            fclose($handle);
        }
    }


    public function fetchLandmark()
    {
        global $wpdb;
        $pop_destinations_path = plugins_url( 'landmark_ids.csv', dirname( __FILE__ ).'/includes/' );
        $row = 1;
        if (($handle = fopen($pop_destinations_path, "r")) !== FALSE) {
            $counter = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $wpdb->query("INSERT INTO ".$wpdb->prefix ."agoda_landmark (landmark_id,city_id,area_id,landmark_name) VALUES('".esc_sql($data[0])."','".esc_sql($data[1])."','".esc_sql($data[2])."','".esc_sql($data[3])."')");

                $counter ++;
                if($counter == $this->max_to_fetch) {
                    break;
                }
            }
            fclose($handle);
        }
    }





}