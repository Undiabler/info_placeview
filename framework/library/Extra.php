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

    public function getLanguageName($lang) {
        $languages = [
            'ru' => 'Русский',
            'en' => 'English'
        ];

        return $languages[$lang];
    }

    public function isUriMultiLang() {
        $currentUri = trim($_SERVER['REQUEST_URI']);

        $Uri = trim($currentUri, '/');
        $UriArray = explode('/', $Uri);

        // check if language exists in current URI
        return (isset($UriArray[0]) && in_array($UriArray[0], (array)$this->config->langs));
    }

    public function getUriInLang($lang) {
        $currentUri = trim($_SERVER['REQUEST_URI']);

        return preg_replace('/^\/\w{2}/', "/$lang", $currentUri);
    }

    public function urlSlug($str, $options = array()) {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => false,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',

            // Latin symbols
            '©' => '(c)',

            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',

            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',

            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',

            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

}