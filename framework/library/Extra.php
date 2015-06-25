<?php

use Phalcon\Mvc\User\Component;
use Phalcon\Logger,
    Phalcon\Events\Manager as EventsManager,
    Phalcon\Logger\Adapter\File as FileLogger;

class Extra extends Component
{
    private $loggers=[];

    private function log_construct($name){
        if (!array_key_exists($name, $this->loggers))
            $this->loggers[$name] = new FileLogger("app/logs/$name.log");
        return $this->loggers[$name];
    }
    public function log($message) {
        $this->log_construct('main')->log($message, Logger::INFO);
    }

    public function logEx($message,$name) {
        $this->log_construct($name)->log($message, Logger::INFO);
    }

    public function getCampsProps($id){
        $row=Props::findFirst(array(
            "conditions" => "id = ?1",
            "bind"       => array(1 => $id)
        ));
        if ($row) return $row->name;
        return "Неопределено";
    }

    public function cache($time=300){ //5 min
        
        $frontCache = new Phalcon\Cache\Frontend\Data(array( 'lifetime' => $time ));
        
        if (strstr($_SERVER["HTTP_HOST"], '.com')) {

            return new Phalcon\Cache\Backend\Xcache($frontCache, array(
                'prefix' => 'app-data'
            ));
        
        } else {
        
            return new Phalcon\Cache\Backend\File($frontCache, array(
                "prefix" => 'cache',
                "cacheDir" => "app/cache/"
            ));
        }

    }

    public function getTodayDone(){
        
        $t=$this->extra->getSql("SELECT COUNT(*) as total, SUM(is_finished) as done from tasks WHERE date = ? AND owner_id = ? ",[date('Y.m.d'),$this->user->getId()]);
        if (count($t)) { $t=$t[0];
            if ($t['total']==0) return -1;
            return ceil($t['done']*100/$t['total']);
        } else
            return -1;

    }

    public function getSnail(){

        $now=date('Y.m.d');
        $week=DateTime::createFromFormat('Y.m.d', $now)->format("W");
        
        $t=$this->extra->getSql("SELECT COUNT(*) as total from tasks WHERE date < ? AND week = ? AND owner_id = ? ",[$now,$week,$this->user->getId()]);

        if ($t[0]['total']>0) return true;
        return false;
    }

    public function getRuDateTime($date){

        $nextday=date('Y.m.d',time()+86400);
        $now=date('Y.m.d');
        $yesterday=date('Y.m.d',time()-86400);

        $pre=($date==$nextday?'Завтра, ':($date==$now?'Сегодня, ':($date==$yesterday?'Вчера, ':'')) );
        
        $date_timestamp = DateTime::createFromFormat('Y.m.d', $date)->getTimestamp();

        $weekday = date("j F Y, D",$date_timestamp);

        $rgSearch = array('Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',

        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',

        'Sun',
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat',
        );

        $rgReplace = array('Понедельник',
        'Вторник',
        'Среда',
        'Четверг',
        'Пятница',
        'Суббота',
        'Воскресенье',

        'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
        'декабря',

        'ВС',
        'ПН',
        'ВТ',
        'СР',
        'ЧТ',
        'ПТ',
        'СБ',

        );
        
        


       return $pre.str_replace($rgSearch, $rgReplace, $weekday);        

    }


    public function getSql($sql,$params=[]){
        $cn=$this->db->query("$sql",$params);
        $cn->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $rows = $cn->fetchAll();
        return $rows;
    }

    public function url($array) {
        if (!array_key_exists('language', $array)) {
            $array['language'] = $this->config->lang;
        }
        return $this->url->get($array);
    }

    public function linkTo($array){
    	$i_a=$array[array_keys($array)[0]];
    	$lang = ['language'=>$this->config->lang];
    	if (is_array($i_a)) {
    		$array[array_keys($array)[0]]=array_merge($i_a,$lang);
    	} else {
    		$array = array_merge($array,$lang);
    	}

    	return $this->tag->linkTo($array);

    }

    public function urlBuild($params=[]){

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $arr=$_GET;
        unset($arr['_url']);
        return $uri_parts[0]."?".http_build_query(array_merge($arr, $params));
    }

    public function getSpheres(){
        $sp=$this->extra->getSql("( SELECT *,1 as owner from spheres WHERE owner_id = ? ORDER BY sort ) 
            UNION ALL 
            (SELECT *,0 as owner from spheres WHERE id in (SELECT sphere_id from spheres_users WHERE user_id = ? AND accepted = 1))",[$this->user->getId(),$this->user->getId()]);
        return $sp;
    }


    public function getInvites(){
        $sp=$this->extra->getSql("SELECT t.id,t.name,t.color,us.firstName,us.lastName from spheres as t LEFT JOIN users as us ON us.id=t.owner_id WHERE t.id in (SELECT sphere_id as id from spheres_users WHERE user_id = ? AND accepted = 0 )",[$this->user->getId()]);
        return $sp;
    }

    public function notify($id,$message){
        $this->db->execute("INSERT INTO notifies(user_id,message) VALUES(?,?)",[$id,$message]);
    }


    public function mail($email,$theme,$body){

        // system('ls');
        require_once '../app/extra/mailer/PHPMailerAutoload.php';


        $mail = new PHPMailer;

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.aimgod.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'hello@aimgod.com';                 // SMTP username
        $mail->Password = 'WWqTJuG9fyfkw$3e';                           // SMTP password
        // $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 25;                                    // TCP port to connect to

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->From = 'hello@aimgod.com';
        $mail->FromName = 'Aimgod.com';
        // $mail->addAddress('undiabler@gmail.com', 'UNDIABLER');     // Add a recipient
        $mail->addAddress($email);               // Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $theme;
        $mail->Body    = $body;
        $mail->AltBody = $body;

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }

    public function mb_ucfirst($string, $encoding)
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    public function getLanguageName($lang) {
        return $this->mb_ucfirst(locale_get_display_language($lang, $lang), 'UTF-8');
    }





}