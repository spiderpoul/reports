<?php 
    
    $login = $_POST['login'];
    $password = $_POST['password'];
    $server_password = "Qwe1234";
    $server_login = "local@ps.new";
    if ($password == $server_password && $login == $server_login) {
            
        $year = $_POST['year'];
        $month = $_POST['month'];    

        $year_last = $year-1;
        if ($month != 1)          
            $month_last = $month-1;
        else
            $month_last = 12;

        if ($month == 1)
            $year_correct = $year - 1;
        else 
            $year_correct = $year;

        $months = array(
            1 => "январь",
            2 => "февраль",
            3 => "март",
            4 => "апрель",
            5 => "май",
            6 => "июнь",
            7 => "июль",
            8 => "август",
            9 => "сентябрь",
            10 => "октябрь",
            11 => "ноябрь",
            12 => "декабрь",   
        );

        header( 'Content-Type: text/html; charset=utf-8' );
        echo "<h2>Отчёт по ИМ за период ",$months[$month]," ",$year,"</h2>";    

        $db['hostname'] = "localhost";
        $db['username'] = "grpzru_shop";
        $db['password'] = 'Fker$TTf4544r';
        $db['database'] = "grpzru_shop";   
        $db['dbdriver'] = "mysql";
        $db['dbprefix'] = "";
        $db['pconnect'] = TRUE;
        $db['db_debug'] = FALSE;
        $db['cache_on'] = FALSE;
        $db['cachedir'] = "";
        $db['char_set'] = "utf8";
        $db['dbcollat'] = "utf8_general_ci";

        $link = mysql_connect($db['hostname'],$db['username'],$db['password']) OR DIE("Не могу создать соединение ");
         // Выборка базы
        mysql_select_db($db['database']);    
        mysql_query("SET NAMES 'utf8'");

        //Все заказы
        $query_all = "
            SELECT
            ps_orders.id_order,        
            ps_customer.lastname,

            ps_orders.total_paid,        

            ps_orders.date_add,
            ps_orders.date_upd,        

            ps_order_state_lang.name AS Status,       	 		
            ps_orders.module,
            ps_orders.payment    

            FROM ps_orders, ps_customer, ps_order_state_lang

            WHERE ps_order_state_lang.id_order_state = ps_orders.current_state 
            AND ps_orders.id_customer = ps_customer.id_customer         
            AND YEAR(ps_orders.date_add) = $year
            AND MONTH(ps_orders.date_add) = $month            
        ";

        $all_orders = mysql_query($query_all) or die('Query failed: ' . mysql_error());

         echo '
            <!DOCTYPE html>
            <style type="text/css">
                table {
                    width: 1100px;                
                    border-collapse: collapse;
                }
                td {
                   border: 1px solid black;
                   padding: 3px;
                }

                table.welders {
                    width: 200px;
                }

                table.counters {
                    width: 600px;
                }

            </style>    
        ';

        $count_orders=0;
        $sum_orders=0;
        echo "<h3>Заказы за период ", $months[$month]," ",$year,"</h3>";
        echo "<table>";
        echo "<tr><td>", "ID заказа", "</td><td>", "ФИО", "</td>";
        echo "<td>", "Сумма заказа", "</td><td>", "Дата", "</td>";
        echo "<td>", "Статус", "</td>";
        echo "<td>", "Оплата", "</td></tr>";

        while ($row = mysql_fetch_array($all_orders)) {
            $count_orders++;
            echo "<tr><td>", $row["id_order"], "</td><td>", $row["lastname"], "</td>";
            echo "<td>", $row["total_paid"], "</td><td>", $row["date_add"], "</td>";
            echo "<td>", $row["Status"], "</td>";        
            echo "<td>", $row["payment"], "</td></tr>";
            $sum_orders = $sum_orders + $row["total_paid"];                
        }    
        echo "</table>";    

        echo "<br> Количество заказов за ", $year,"-", $month," = ", $count_orders;
        echo "<br> Сумма заказов за ", $year,"-", $month," = ", $sum_orders, " руб.";
        echo "<br><br>";

        //
        // Продажи за текущий период
        //
        $query_sales = "
            SELECT
            ps_orders.id_order,  
            ps_orders.id_customer,
            ps_customer.lastname,        

            ps_orders.total_paid,        

            ps_orders.date_add,
            ps_orders.date_upd,        

            ps_order_state_lang.name AS Status,       	 
            ps_order_payment.date_add As date_payment,
            ps_orders.module,
            ps_orders.current_state, 
            ps_orders.payment    

            FROM ps_orders, ps_customer, ps_order_state_lang, ps_order_payment

            WHERE ps_order_state_lang.id_order_state = ps_orders.current_state 
            AND ps_orders.id_customer = ps_customer.id_customer 
            AND ps_order_payment.order_reference = ps_orders.reference
            AND YEAR(ps_orders.date_add) = $year
            AND MONTH(ps_orders.date_add) = $month  
            AND MONTH(ps_order_payment.date_add) = $month 
        ";

        $sales = mysql_query($query_sales) or die('Query failed: ' . mysql_error());    

        $sum_sales = 0;
        $sum_reg_deal = 0;
        $count_reg_deal = 0;
        $sum_web_deal = 0;    
        $sum_payu = 0;
        $count_payu = 0;
        $count_sales = 0;
        $dealer_state = 16;
        $web_dealers = array(521,534);

        echo "<h3>Продажи за  ", $months[$month]," ",$year,"</h3>";
        echo "<table>";
        echo "<tr><td>", "id_order", "</td><td>", "id_customer", "</td><td>", "lastname", "</td>";
        echo "<td>", "total_paid", "</td><td>", "date_add", "</td>";
        echo "<td>", "date_upd", "</td><td>", "Status", "</td>";
        echo "<td>", "date_payment", "</td><td>", "module", "</td>";
        echo "<td>", "current_state", "</td><td>", "payment", "</td></tr>";

        while ($row = mysql_fetch_array($sales)) {    
            echo "<tr><td>", $row["id_order"], "</td><td>", $row["id_customer"], "</td><td>", $row["lastname"], "</td>";
            echo "<td>", $row["total_paid"], "</td><td>", $row["date_add"], "</td>";
            echo "<td>", $row["date_upd"], "</td><td>", $row["Status"], "</td>";
            echo "<td>", $row["date_payment"], "</td><td>", $row["module"], "</td>";
            echo "<td>", $row["current_state"], "</td><td>", $row["payment"], "</td></tr>";

            $sum_orders = $sum_orders + $row["total_paid"];       
            $sum_sales = $sum_sales + $row["total_paid"];            
            $count_sales++;                        

            if (($row["module"] == "payu") && ($row["current_state"] != $dealer_state)){
                $sum_payu = $sum_payu + $row["total_paid"];
                $count_payu++;
            }

            if ($row["current_state"] == $dealer_state) {
                $sum_reg_deal = $sum_reg_deal + $row["total_paid"];
                $count_reg_deal++;
            }


            foreach($web_dealers as $value)
                {
                    if ($row["id_customer"] == $value)
                        $sum_web_deal = $sum_web_deal + $row["total_paid"];
            }
        }    
        echo "</table>";    
        /*echo "<br> Сумма продаж за ", $year,"-", $month," = ", $sum_sales, " руб.";
        echo "<br> Количество оплаченных заказов за ", $year,"-", $month," = ", $count_sales;*/
        echo "<br><br>";
        
        //
        //Прошлые периоды
        //


        $query_past = "
            SELECT
            ps_orders.id_order,
            ps_orders.id_customer,
            ps_customer.lastname,

            ps_orders.total_paid,        

            ps_orders.date_add As date_add,
            ps_order_payment.date_add As date_payment,

            ps_order_state_lang.name AS Status,       	 
            ps_orders.current_state,
            ps_orders.module,        
            ps_orders.payment    

            FROM ps_orders, ps_customer, ps_order_state_lang, ps_order_payment 

            WHERE ps_order_state_lang.id_order_state=ps_orders.current_state 
            AND ps_orders.id_customer = ps_customer.id_customer
            AND ps_orders.reference = ps_order_payment.order_reference
            AND YEAR(ps_orders.date_add) = $year_correct        
            AND (NOT MONTH(ps_orders.date_add) = $month)
            AND MONTH(ps_order_payment.date_add) = $month 
        ";

        $all_past = mysql_query($query_past) or die('Query failed: ' . mysql_error());    
        echo "<h3>Оплаты с прошлых периодов на период ", $months[$month]," ",$year,"</h3>";
        echo "<table>";
        echo "<tr><td>", "id_order", "</td><td>", "id_customer", "</td><td>", "lastname", "</td>";
        echo "<td>", "total_paid", "</td><td>", "date_add", "</td>";
        echo "<td>", "date_payment", "</td><td>", "Status", "</td>";
        echo "<td>", "current_state", "</td><td>", "module", "</td>";
        echo "<td>", "payment", "</td></tr>";

        $sum_past = 0;
        $count_past = 0;
        while ($row = mysql_fetch_array($all_past)) {        
            echo "<tr><td>", $row["id_order"], "</td><td>", $row["id_customer"], "</td><td>", $row["lastname"], "</td>";
            echo "<td>", $row["total_paid"], "</td><td>", $row["date_add"], "</td>";
            echo "<td>", $row["date_payment"], "</td><td>", $row["Status"], "</td>";
            echo "<td>", $row["current_state"], "</td><td>", $row["module"], "</td>";
            echo "<td>", $row["payment"], "</td></tr>";        
            $sum_past = $sum_past + $row["total_paid"];
            $count_past++;
            $count_sales++;
            $sum_sales = $sum_sales + $row["total_paid"];

            if ($row["current_state"] == $dealer_state) {
                $sum_reg_deal = $sum_reg_deal + $row["total_paid"];
                $count_reg_deal++;
            }                        

            if (($row["module"] == "payu") && ($row["current_state"] != $dealer_state)){
                    $sum_payu = $sum_payu + $row["total_paid"];
                    $count_payu++;
            }

            foreach($web_dealers as $value)
            {
                    if ($row["id_customer"] == $value)
                        $sum_web_deal = $sum_web_deal + $row["total_paid"];
            }
        }    
        echo "</table>";    
        echo "<br> Всего сумма продаж за ", $year,"-", $month," = ", $sum_sales, " руб.";
        echo "<br> Общее количество оплаченных заказов за ", $year,"-", $month," = ", $count_sales;
        echo "<br>";
        echo "<br> Всего сумма продаж через региональных дилеров за ", $year,"-", $month," = ", $sum_reg_deal, " руб.", " Количество заказов: ", $count_reg_deal;
        echo "<br> Всего сумма продаж через интернет-дилеров за ", $year,"-", $month," = ", $sum_web_deal, " руб.";
        echo "<br> Всего оплачено через PayU за ", $year,"-", $month," = ", $sum_payu, " руб.";
        echo "<br> Количество оплаченных заказов через PayU за ", $year,"-", $month," = ", $count_payu;
        echo "<br>";
        echo "<br> Сумма оплат с прошлых периодов ", $year,"-", $month," = ", $sum_past, " руб.";
        echo "<br> Количество оплаченных заказов с прошлых периодов ", $year,"-", $month," = ", $count_past;
        //*******************************************//

        //
        // Отказы
        //

        // Отказы за текущий период

        $refusal_state = 19;
        $sum_refusal = 0;
        $count_refusal = 0;

        function get_refusal($refusal_state, $month, $year){
            $query_refusal = "
                SELECT
                ps_orders.id_order,  
                ps_orders.id_customer,
                ps_customer.lastname,        

                ps_orders.total_paid,        

                ps_orders.date_add,
                ps_orders.date_upd,        

                ps_order_state_lang.name AS Status,       	 
                ps_customer.note,
                ps_orders.module,
                ps_orders.current_state, 
                ps_orders.payment    

                FROM ps_orders, ps_customer, ps_order_state_lang

                WHERE ps_order_state_lang.id_order_state = ps_orders.current_state 
                AND ps_orders.id_customer = ps_customer.id_customer    
                AND ps_orders.current_state = $refusal_state
                AND YEAR(ps_orders.date_add) = $year
                AND MONTH(ps_orders.date_add) = $month
                AND MONTH(ps_orders.date_upd) = $month
            ";
            $refusal = mysql_query($query_refusal) or die('Query failed: ' . mysql_error());
            return $refusal;   
        }          

        $refusal = get_refusal($refusal_state, $month, $year);
        If ($refusal != NULL) {
            echo "<h3>Отказы за  ", $months[$month]," ",$year,"</h3>";
            echo "<table>";
            echo "<tr><td>", "Номер заказа", "</td>", "</td>";
            echo "<td>", "Сумма, руб.", "</td>", "</td>";
            echo "<td>", "Причины отказа", "</td>", "</td></tr>";   
            while ($row = mysql_fetch_array($refusal)) {        
                echo "<tr><td>", $row["id_order"], "</td>";
                echo "<td>", $row["total_paid"], "</td>";
                echo "<td>", $row["note"], "</td></tr>";
                $sum_refusal = $sum_refusal + $row["total_paid"];
                $count_refusal++;
            }       
        }

        // Отказы за прошлый период

        function get_refusal_past($refusal_state, $month, $year_correct) {
            $query_refusal_past = "
                SELECT
                ps_orders.id_order,  
                ps_orders.id_customer,
                ps_customer.lastname,        

                ps_orders.total_paid,        

                ps_orders.date_add,
                ps_orders.date_upd,        

                ps_order_state_lang.name AS Status,       	 
                ps_customer.note,
                ps_orders.module,
                ps_orders.current_state, 
                ps_orders.payment    

                FROM ps_orders, ps_customer, ps_order_state_lang

                WHERE ps_order_state_lang.id_order_state = ps_orders.current_state 
                AND ps_orders.id_customer = ps_customer.id_customer
                AND ps_orders.current_state = $refusal_state
                AND YEAR(ps_orders.date_add) = $year_correct        
                AND (NOT MONTH(ps_orders.date_add) = $month)
                AND MONTH(ps_orders.date_upd) = $month         
            ";
            $refusal_past = mysql_query($query_refusal_past) or die('Query failed: ' . mysql_error());
            return $refusal_past;    
        }

        $refusal_past = get_refusal_past($refusal_state, $month, $year_correct);
        If ($refusal_past != NULL) {        
            echo "<tr><td colspan='3'>","Отказы за прошлый период","</td></tr>";

            while ($row = mysql_fetch_array($refusal_past)) {        
                echo "<tr><td>", $row["id_order"], "</td>";
                echo "<td>", $row["total_paid"], "</td>";
                echo "<td>", $row["note"], "</td></tr>";
                $sum_refusal = $sum_refusal + $row["total_paid"];
                $count_refusal++;        
            }  
            echo "<tr><td><b>Итого</b></td><td><b>",$sum_refusal,"</b></td><td></td></tr>";
            echo "</table>";    
        } 
        else {
            echo "<tr><td><b>Итого</b></td><td><b>",$sum_refusal,"</b></td><td></td></tr>";
            echo "</table>";
        }        
        echo "<br> Сумма отказов всего за ", $year,"-", $month," = ", $sum_refusal, " руб.";
        echo "<br> Количество отказов всего за ", $year,"-", $month," = ", $count_refusal;
        echo "<br><br>";

        // Сравнение с прошлыми отказами
        $sum_refusal_last = 0;
        $refusal_last = get_refusal($refusal_state, $month_last, $year_correct);
        if ($refusal_last != NULL)
        {
            while ($row = mysql_fetch_array($refusal_last)) {
                $sum_refusal_last = $sum_refusal_last + $row["total_paid"];
            }
        }

        $refusal_past_last = get_refusal_past($refusal_state, $month_last, $year_correct);
        if ($refusal_past_last != NULL) {
            while ($row = mysql_fetch_array($refusal_past_last)) {
                $sum_refusal_last = $sum_refusal_last + $row["total_paid"];
            }
        }
        $difference = "";
        if ($sum_refusal_last < $sum_refusal) {
            $difference = "выше";
            $sum_refusal_difference = $sum_refusal - $sum_refusal_last;
        }
        else {
            $difference = "ниже"; 
            $sum_refusal_difference = $sum_refusal_last - $sum_refusal;
        }

        echo "<br> Сумма отказов по сравнению с прошлым периодом ",$difference," на ", $sum_refusal_difference, " руб.";
        echo "<br><br>";

        //**********************************************//

        //
        //Выборка по приборам ЗАКАЗЫ
        //
        function return_product($year, $month) {
            $query_product = "
                SELECT
                ps_order_detail.id_order,
                ps_order_detail.product_id,
                ps_order_detail.product_name,
                ps_order_detail.product_quantity AS Kolich,
                ps_order_detail.total_price_tax_incl AS Total_price,

                ps_orders.date_add,
                ps_orders.date_upd,

                ps_order_state_lang.name AS Status,
                ps_order_invoice.total_discount_tax_incl AS Skidka

                FROM ps_order_detail, ps_orders, ps_order_invoice , ps_order_state_lang 

                WHERE ps_order_detail.id_order=ps_orders.id_order
                AND ps_order_state_lang.id_order_state=ps_orders.current_state 
                AND ps_order_invoice.id_order=ps_order_detail.id_order
                AND YEAR(ps_orders.date_add) = $year
                AND MONTH(ps_orders.date_add) = $month
            ";

            $product = mysql_query($query_product) or die('Query failed: ' . mysql_error());
            return $product;
        }


        $sum_orders = 0;
        $welders = array(
            10 => "Форсаж-161",
            11 => "Форсаж-180",
            12 => "Форсаж-200",
            13 => "Форсаж-200М",
            14 => "Форсаж-301",
            15 => "Форсаж-315М",
            9 => "Форсаж-200ПА",
            35 => "Форсаж-302",
            36 => "Форсаж-502",
            38 => "Форсаж-201АД",
            39 => "Форсаж-315АД",
            40 => "Форсаж-315AC/DC",
            117 => "Форсаж-200АС/DC",
            124 => "Форсаж-500АС/DC",
            119 => "Форсаж-70П",
            37 => "Форсаж-МПм",
            17 => "Форсаж- МП5",
            118 => "Форсаж- МПЦ-02",
        );

        $all_welders = array(
            10 => "Форсаж-161",
            11 => "Форсаж-180",
            12 => "Форсаж-200",
            13 => "Форсаж-200М",
            14 => "Форсаж-301",
            15 => "Форсаж-315М",
            9 => "Форсаж-200ПА",
            35 => "Форсаж-302",
            36 => "Форсаж-502",
            38 => "Форсаж-201АД",
            39 => "Форсаж-315АД",
            40 => "Форсаж-315AC/DC",
            117 => "Форсаж-200АС/DC",
            124 => "Форсаж-500АС/DC",
            119 => "Форсаж-70П",
            37 => "Форсаж-МПм",
            17 => "Форсаж- МП5",
            118 => "Форсаж- МПЦ-02",
            46 => "Горелка для Форсаж-200ПА",
            18 => "Горелка для Форсаж-201АД, 200AC/DC",
            45 => "Горелка для Форсаж-315АД, 315AC/DC",
            47 => "Горелка для Форсаж-МП5, Форсаж-МПм, Форсаж-МПЦ02",
            41 => "Кабель (до 200А) длиной 2,5м",
            42 => "Кабель (до 200А) длиной 5м",
            43 => "Кабель (до 500А) длиной 5 метров",
            19 => "Комплект дооснащения к ФОРСАЖ-200ПА",
            49 => "ПДУ к ФОРСАЖ-200М",
            48 => "ПДУ к ФОРСАЖ-301, 302, 315М, 502",
            50 => "ПДУ к ФОРСАЖ-315 АД, 315 АС/DC",
            122 => "Форсаж-502 расширенная модификация",

        );

        $meds = array(
            28 => "ТГДц - 01",
            120 => "ТГДц - 03",
            29 => "ИГД - 02",
            30 => "ИГД - 03",
            31 => "АМТО-01",
            123 => "АМТО-02",
        );

        $electros = array(
            20 => "СЭТ 3а 01-22-01",
            51 => "СЭТ 3а 01-24-02",
            52 => "СЭТ 3а 02-34-03",
            53 => "СЭТ 3а 02-44-04",
            54 => "СЭТ 3а 02-64-05",
            116 => "СЭТ 3а 02-64-05-Ш-Д",
            55 => "СЭТ 3а 02-74-06",
            24 => "СЭТ 3а 02Т-34-03-С1(С2)-ЖКИ",
            125 => "СЭТ 3а 02Т-34-16",
            64 => "СЭТ 3а 02Т-34-М3-С1(С2)-ЖКИ",
            61 => "СЭТ 3а 02Т-44-04-С1(С2)-ЖКИ",
            23 => "СЭТ 3а 02Т-44-17",
            65 => "СЭТ 3а 02Т-44-М4-С1(С2)-ЖКИ",
            62 => "СЭТ 3а 02Т-64-05-С1(С2)-ЖКИ",
            63 => "СЭТ 3а 02Т-74-06-С1(С2)-ЖКИ",
            66 => "СЭТ 3а 02Т-74-М6-С1(С2)-ЖКИ",
            127 => "СЭТ 3ар 01Т-24-09-С1-ЖКИ",
            69 => "СЭТ 3ар 01Т-24-Н9-С1-ЖКИ",
            57 => "СЭТ 3ар 02-44-11",
            70 => "СЭТ 3ар 02Т-34-Н10-С1-ЖКИ",
            71 => "СЭТ 3ар 02Т-44-Н11-С1-ЖКИ",
            21 => "СЭТ 3р 01-22-08",
            126 => "СЭТ 3р 01П-22-30",
            34 => "СЭТ 3р 02-34-10",
            56 => "СЭТ 3р-01-24-09",
        );

        $count_welders = array();
        $count_welders_last_month = array();
        $count_welders_last_year = array();
        $sum_welders = 0;

        $count_meds = array();    
        $count_meds_last_month = array();
        $count_meds_last_year = array();
        $sum_meds = 0;   

        $sum_electros = 0;
        $sum_electro_one = array();
        $count_electros = array();  


        $product_this = return_product($year, $month);  
        $product_last_year = return_product($year_last, $month);  
        $product_last_month = return_product($year_correct, $month_last);

        while ($row = mysql_fetch_array($product_this)){        
            $sum_orders = $sum_orders + $row["Total_price"]-$row["Skidka"];

            foreach($welders as $id=>$welder){
                if ($row["product_id"] == $id) {
                    $count_welders[$id] = $count_welders[$id] + $row["Kolich"];
                }                       
            }

            foreach($all_welders as $id=>$welder){
                if ($row["product_id"] == $id)
                    $sum_welders = $sum_welders + $row["Total_price"]-$row["Skidka"];
            }

            foreach($meds as $id=>$med){
                if ($row["product_id"] == $id) {
                    $count_meds[$id] = $count_meds[$id] + $row["Kolich"];
                    $sum_meds = $sum_meds + $row["Total_price"] - $row["Skidka"];
                }                
            }

            foreach($electros as $id=>$electro){
                if ($row["product_id"] == $id) {                
                    $sum_electros = $sum_electros + $row["Total_price"] - $row["Skidka"];
                    $sum_electro_one[$id] = $sum_electro_one[$id] + $row["Total_price"] - $row["Skidka"];
                    $count_electros[$id] = $count_electros[$id] + $row["Kolich"];
                }                
            }
        }    
        echo "</table>";

        while ($row_last_month = mysql_fetch_array($product_last_month)){
            foreach($welders as $id=>$welder){                        
                if ($row_last_month["product_id"] == $id)
                    $count_welders_last_month[$id] = $count_welders_last_month[$id] + $row_last_month["Kolich"];
            }

            foreach($meds as $id=>$med)
                if ($row_last_month["product_id"] == $id)
                    $count_meds_last_month[$id] = $count_meds_last_month[$id] + $row_last_month["Kolich"];                            
        }    

        while ($row_last_year = mysql_fetch_array($product_last_year)) {
            foreach($welders as $id=>$welder){                        
                if ($row_last_year["product_id"] == $id)
                    $count_welders_last_year[$id] = $count_welders_last_year[$id] + $row_last_year["Kolich"];
            }

             foreach($meds as $id=>$med)
                if ($row_last_year["product_id"] == $id)
                    $count_meds_last_year[$id] = $count_meds_last_year[$id] + $row_last_year["Kolich"]; 
        }                

        //
        // Заказы по приборам сварочники
        //
        function sum_mass($mass) {
            $sum = 0;
            foreach($mass as $value)
                $sum = $sum + $value;
            return $sum;
        }

        echo "<h3>Заказы по приборам сварочники за период ", $months[$month]," ",$year,"</h3>";
        echo '<table class="welders">';
        echo "<tr><td>","", "</td><td>", $months[$month]," ",$year_last, "</td>";
        echo "<td>", $months[$month_last]," ",$year_correct, "</td>";
        echo "<td>", $months[$month]," ",$year, "</td>","</tr>";

        foreach($welders as $id=>$welder) {
            echo "<tr><td>", $welder, "</td><td>", $count_welders_last_year[$id], "</td>";        
            echo "<td>",$count_welders_last_month[$id],"</td>";
            echo "<td>",$count_welders[$id],"</td></tr>";
        }
        echo "</table>";
        echo "<br> Общее количество заказанных сварочников за ", $year,"-", $month," = ", sum_mass($count_welders);
        echo "<br> Сумма заказов сварочников за ", $year,"-", $month," = ", $sum_welders, " руб.";

        //
        // Выборка по приборам ПРОДАЖИ
        //

        function return_product_sell($year, $month) {
            $query_product = "
                SELECT
                ps_order_detail.id_order,
                ps_order_detail.product_id,
                ps_order_detail.product_name,
                ps_order_detail.product_quantity AS Kolich,
                ps_order_detail.total_price_tax_incl AS Total_price,

                ps_orders.date_add,
                ps_order_payment.date_add As date_payment,

                ps_order_state_lang.name AS Status,
                ps_order_invoice.total_discount_tax_incl AS Skidka

                FROM ps_order_detail, ps_orders, ps_order_invoice , ps_order_state_lang, ps_order_payment 

                WHERE ps_order_detail.id_order=ps_orders.id_order
                AND ps_order_state_lang.id_order_state=ps_orders.current_state 
                AND ps_order_payment.order_reference = ps_orders.reference
                AND ps_order_invoice.id_order=ps_order_detail.id_order
                AND YEAR(ps_order_payment.date_add) = $year
                AND MONTH(ps_order_payment.date_add) = $month 
            ";

            $product = mysql_query($query_product) or die('Query failed: ' . mysql_error());
            return $product;
        }


        $sum_orders_sell = 0;

        $count_welders_sell = array();
        $count_welders_last_month_sell = array();
        $count_welders_last_year_sell = array();
        $sum_welders_sell = 0;

        $count_meds_sell = array();    
        $count_meds_last_month_sell = array();
        $count_meds_last_year_sell = array();
        $sum_meds_sell = 0;    

        $count_meds_sell = array();        
        $sum_meds_sell = 0; 

        $count_electros_sell = array();
        $sum_electro_one_sell = array();
        $sum_electros_sell = 0;

        $product_this_sell = return_product_sell($year, $month);  
        $product_last_year_sell = return_product_sell($year_last, $month);  
        $product_last_month_sell = return_product_sell($year_correct, $month_last);


        while ($row = mysql_fetch_array($product_this_sell)){        

            $sum_orders_sell = $sum_orders_sell + $row["Total_price"]-$row["Skidka"];

            foreach($welders as $id=>$welder){
                if ($row["product_id"] == $id) {
                    $count_welders_sell[$id] = $count_welders_sell[$id] + $row["Kolich"];
                }                       
            }

            foreach($all_welders as $id=>$welder){
                if ($row["product_id"] == $id)
                    $sum_welders_sell = $sum_welders_sell + $row["Total_price"]-$row["Skidka"];
            }

            foreach($meds as $id=>$med){
                if ($row["product_id"] == $id) {
                    $count_meds_sell[$id] = $count_meds_sell[$id] + $row["Kolich"];
                    $sum_meds_sell = $sum_meds_sell + $row["Total_price"]-$row["Skidka"];
                }                
            }

            foreach($electros as $id=>$electro)
                if ($row["product_id"] == $id) {
                    $count_electros_sell[$id] = $count_electros_sell[$id] + $row["Kolich"];
                    $sum_electro_one_sell[$id] = $sum_electro_one_sell[$id] + + $row["Total_price"]-$row["Skidka"];
                    $sum_electros_sell = $sum_electros_sell + $row["Total_price"]-$row["Skidka"];
                } 
        }    
        echo "</table>";

        while ($row_last_month = mysql_fetch_array($product_last_month_sell)){
            foreach($welders as $id=>$welder){                        
                if ($row_last_month["product_id"] == $id)
                    $count_welders_last_month_sell[$id] = $count_welders_last_month_sell[$id] + $row_last_month["Kolich"];
            }

            foreach($meds as $id=>$med)
                if ($row_last_month["product_id"] == $id)
                    $count_meds_last_month_sell[$id] = $count_meds_last_month_sell[$id] + $row_last_month["Kolich"];                            
        }    

        while ($row_last_year = mysql_fetch_array($product_last_year_sell)) {
            foreach($welders as $id=>$welder){                        
                if ($row_last_year["product_id"] == $id)
                    $count_welders_last_year_sell[$id] = $count_welders_last_year_sell[$id] + $row_last_year["Kolich"];
            }

             foreach($meds as $id=>$med)
                if ($row_last_year["product_id"] == $id)
                    $count_meds_last_year_sell[$id] = $count_meds_last_year_sell[$id] + $row_last_year["Kolich"]; 
        }    

        echo "<br><br>";

        //
        // ПРОДАЖИ по приборам сварочники
        //

        function difference_absolute($a, $b) {
            if (($b != NULL) & ($a != NULL)) {
                if ($a == $b)
                    return "равно";
                if ($a > $b) {
                    $difference = number_format((($a-$b)/$a)*100, 0 , ',' , '');
                    return "ниже на $difference%";
                }
                else {
                    $difference = number_format((($b-$a)/$b)*100, 0 , ',' , '');
                    return "выше на $difference%";
                }
            }
            else
                return "-";
        }

        echo "<h3>Продажи по приборам сварочники за период ", $months[$month]," ",$year,"</h3>";
        echo '<table class="welders">';
        echo "<tr><td>","", "</td><td>","Заказы ", $months[$month]," ",$year, "</td>";
        echo "<td>","Продажи ", $months[$month_last]," ",$year_correct, "</td>";
        echo "<td>","Продажи ", $months[$month]," ",$year, "</td>";
        echo "<td>","Сравнение с прошлым месяцем, %" , "</td>","</tr>";

        foreach($welders as $id=>$welder) {
            echo "<tr><td>", $welder, "</td><td>", $count_welders[$id], "</td>";        
            echo "<td>",$count_welders_last_month_sell[$id],"</td>";
            echo "<td>",$count_welders_sell[$id],"</td>";
            echo "<td>",difference_absolute($count_welders_last_month_sell[$id], $count_welders_sell[$id]),"</td></tr>";
        }
        echo "</table>";
        echo "<br> Общее количество проданных сварочников за ", $year,"-", $month," = ", sum_mass($count_welders_sell);
        echo "<br> Сумма продаж сварочников за ", $year,"-", $month," = ", $sum_welders_sell, " руб.";

        //
        // Заказы по приборам медицина
        //
        echo "<h3>Заказы по медицинским приборам за период ", $months[$month]," ",$year,"</h3>";
        echo '<table class="welders">';
        echo "<tr><td>","", "</td><td>", $months[$month]," ",$year_last, "</td>";
        echo "<td>", $months[$month_last]," ",$year_correct, "</td>";
        echo "<td>", $months[$month]," ",$year, "</td>","</tr>";

        foreach($meds as $id=>$med) {
            echo "<tr><td>", $med, "</td><td>", $count_meds_last_year[$id], "</td>";     
            echo "<td>",$count_meds_last_month[$id],"</td>";
            echo "<td>",$count_meds[$id],"</td></tr>";        
        }
        echo "</table>";
        echo "<br> Общее количество заказанных медицинских приборов за ", $year,"-", $month," = ", sum_mass($count_meds);
        echo "<br> Сумма заказов медицинских приборов за ", $year,"-", $month," = ", $sum_meds, " руб.";
        //*****************************************************************

        //
        // ПРОДАЖИ по приборам медицина
        //  

        echo "<h3>Продажи по медицинским приборам за период ", $months[$month]," ",$year,"</h3>";
        echo '<table class="welders">';
        echo "<tr><td>","", "</td><td>","Заказы ", $months[$month]," ",$year, "</td>";
        echo "<td>","Продажи ", $months[$month_last]," ",$year_correct, "</td>";
        echo "<td>","Продажи ", $months[$month]," ",$year, "</td>";
        echo "<td>","Сравнение с прошлым месяцем, %" , "</td>","</tr>";

        foreach($meds as $id=>$med) {
            echo "<tr><td>", $med, "</td><td>", $count_meds[$id], "</td>";     
            echo "<td>",$count_meds_last_month_sell[$id],"</td>";
            echo "<td>",$count_meds_sell[$id],"</td>"; 
            echo "<td>",difference_absolute($count_meds_last_month_sell[$id], $count_meds_sell[$id]),"</td></tr>";
        }
        echo "</table>";
        echo "<br> Общее количество проданных медицинских приборов за ", $year,"-", $month," = ", sum_mass($count_meds_sell);
        echo "<br> Сумма проданных приборов за ", $year,"-", $month," = ", $sum_meds_sell, " руб.";

        //
        // ПРОДАЖИ по счётчикам
        //
        echo "<h3>Продажи по счётчикам за период ", $months[$month]," ",$year,"</h3>";
        echo '<table class="counters">';
        echo "<tr><td>","Прибор", "</td><td>К-во заказов" , "</td>";
        echo "<td>Сумма заказов" , "</td>";
        echo "<td>К-во продаж" , "</td>";
        echo "<td>Сумма продаж", "</td></tr>";

        foreach($electros as $id=>$electro) {
            echo "<tr><td>", $electro, "</td><td>", $count_electros[$id], "</td>";
            echo "<td>", $sum_electro_one[$id], "</td><td>", $count_electros_sell[$id], "</td>"; 
            echo "<td>", $sum_electro_one_sell[$id], "</td></tr>"; 
        }
        echo "<tr><td></td>","<td>","Заказов на сумму, руб. ", "</td><td>", $sum_electros, "</td>";
        echo "<td>","Продаж на сумму, руб. ", "</td><td>", $sum_electros_sell, "</td></tr>";
        echo "</table>";
        echo "<br> Общее количество проданных счётчиков за ", $year,"-", $month," = ", sum_mass($count_electros_sell);
        echo "<br> Сумма проданных счётчиков за ", $year,"-", $month," = ", $sum_electros_sell, " руб.";

        //
        // Итоговая таблица для приложения
        //
        echo "<br><br>";
        echo "Итоговая таблица для приложения";    
        echo "<table>";

            echo "<tr><td>"," ", "</td><td>", "Итого заказов через ИМ" , "</td>";
            echo "<td>", "Оплачено заказов через ИМ с прошлых периодов" , "</td><td>", "Итого продажи через ИМ" , "</td>";
            echo "<td>", "Отказов через ИМ", "</td>";
            echo "<td>", "Оплачено банковской картой через ИМ", "</td>"; 
            echo "<td>", "Оформлено в ИМ, но куплено у дилера", "</td></tr>";

            echo "<tr><td>", "К-во", "</td><td>", $count_orders, "</td>";
            echo "<td>", $count_past, "</td><td>", $count_sales, "</td>";
            echo "<td>", $count_refusal, "</td>"; 
            echo "<td>", $count_payu, "</td>"; 
            echo "<td>", $count_reg_deal, "</td></tr>";

            echo "<tr><td>", "Сумма, руб.", "</td><td>", $sum_orders, "</td>";
            echo "<td>", $sum_past, "</td><td>", $sum_sales, "</td>";
            echo "<td>", $sum_refusal, "</td>"; 
            echo "<td>", $sum_payu, "</td>"; 
            echo "<td>", $sum_reg_deal, "</td></tr>";

        echo "</table>";
        
         //
         // Купоны и скидки
         //  
        
        $query_discount = "
            SELECT
            ps_cart_cart_rule.id_cart_rule,
            ps_cart_cart_rule.id_cart,
            ps_cart_rule_lang.name As DiscountName,
            
            ps_orders.id_order,        
            ps_customer.lastname,

            ps_orders.total_paid,        

            ps_orders.date_add,
            ps_orders.date_upd,        

            ps_order_state_lang.name AS Status,       	 		
            ps_orders.module,
            ps_orders.payment                        

            FROM ps_orders, ps_customer, ps_order_state_lang, ps_cart_cart_rule, ps_cart_rule_lang

            WHERE ps_order_state_lang.id_order_state = ps_orders.current_state 
            AND ps_orders.id_customer = ps_customer.id_customer         
            AND YEAR(ps_orders.date_add) = $year
            AND  ps_cart_cart_rule.id_cart = ps_orders.id_cart
            AND ps_cart_cart_rule.id_cart_rule = ps_cart_rule_lang.id_cart_rule
        ";

        $discount_orders = mysql_query($query_discount) or die('Query failed: ' . mysql_error());         

        $count_orders=0;
        $sum_orders=0;
        echo "<h3>Купоны за ",$year," год </h3>";
        echo "<table>";
        echo "<tr><td>","Купон", "</td><td>", "ID заказа", "</td><td>", "ФИО", "</td>";
        echo "<td>", "Сумма заказа", "</td><td>", "Дата", "</td>";
        echo "<td>", "Статус", "</td>";
        echo "<td>", "Оплата", "</td></tr>";

        while ($row = mysql_fetch_array($discount_orders)) {
            $count_orders++;
            echo "<tr><td>",$row["DiscountName"],"</td><td>", $row["id_order"], "</td><td>", $row["lastname"], "</td>";
            echo "<td>", $row["total_paid"], "</td><td>", $row["date_add"], "</td>";
            echo "<td>", $row["Status"], "</td>";        
            echo "<td>", $row["payment"], "</td></tr>";
            $sum_orders = $sum_orders + $row["total_paid"];                
        }    
        echo "</table>"; 
        
        //
        // Продажи по приборам(без дилеров)
        //
        /*
        $query_sales_pribors = "
            SELECT
                ps_order_detail.id_order,
                ps_order_detail.product_name,
                ps_order_detail.product_quantity AS Kolich,
                ps_orders.id_customer,
                ps_customer.lastname,        
                
                ps_order_detail.total_price_tax_incl AS Total_price,
                ps_orders.date_add,
                ps_orders.date_upd,
                ps_orders.current_state,

                ps_order_state_lang.name AS Status,
                ps_order_invoice.total_discount_tax_incl AS Skidka

            FROM 
                ps_order_detail, ps_orders, ps_order_invoice , ps_order_state_lang, ps_order_payment, ps_customer
            WHERE 
                ps_order_detail.id_order=ps_orders.id_order
                AND ps_order_state_lang.id_order_state=ps_orders.current_state
                AND ps_order_invoice.id_order=ps_order_detail.id_order
                AND ps_orders.id_customer = ps_customer.id_customer
                AND ps_order_payment.order_reference = ps_orders.reference
                AND YEAR(ps_order_payment.date_add) = $year_correct       
                AND MONTH(ps_order_payment.date_add) = $month
        ";

        $sales_pribors = mysql_query($query_sales_pribors) or die('Query failed: ' . mysql_error());    
        
        echo "<h3>Продажи за  по приборам без дилеров ", $months[$month]," ",$year,"</h3>";
        echo "<table>";
        echo "<tr><td>", "id_order", "</td><td>", "product_name", "</td>";
        echo "<td>", "Kolich", "</td><td>","Total_price", "</td>";
        echo "<td>", "id_customer", "</td><td>", "date_add", "</td>";
        echo "<td>", "date_upd", "</td><td>", "Status", "</td>";
        echo "<td>", "Skidka", "</td></tr>";    

        while ($row = mysql_fetch_array($sales_pribors)) {                
            if ($row["current_state"] != $dealer_state && $row["current_state"] != $refusal_state) {            
                $is_web_dealers = false;
                foreach($web_dealers as $value)
                    if ($row["id_customer"] == $value)
                        $is_web_dealers = true;
            
                if(!$is_web_dealers) {
                    echo "<tr><td>", $row["id_order"], "</td><td>", $row["product_name"], "</td>";
                    echo "<td>", $row["Kolich"], "</td><td>", $row["Total_price"]-$row["Skidka"], "</td>";
                    echo "<td>", $row["lastname"], "</td><td>", $row["date_add"], "</td>";
                    echo "<td>", $row["date_upd"], "</td><td>", $row["Status"], "</td>";
                    echo "<td>", $row["Skidka"], "</td></tr>";
                }
            }    
        }    
        echo "</table>";    
        echo "<br><br>"; 
        */       
    }
    else {
        echo "Введён неправильный пароль" ;
    }
?>

