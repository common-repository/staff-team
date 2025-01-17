<?php

/**
 * Create MO file from PO
 * @category   Language
 * @author     10Web 
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version    Release: 1.0.1
 * */
if (!class_exists('StaffDirLangClass')) {

    class StaffDirLangClass {

        private $SL_langForPostType;
        private $SL_langPluginName;
        private $SL_langDirPath;
        private $SL_langPluginDirPath;

        public function __construct($post_type, $PluginName) {
            $this->SL_langForPostType = $post_type;
            $this->SL_langPluginName = $PluginName;
            $this->SL_langDirPath = WP_CONTENT_DIR . '/uploads/Languages_WD/' . $this->SL_langPluginName . '/';
            $this->SL_langPluginDirPath = WP_CONTENT_DIR . '/plugins/' . $this->SL_langPluginName . '/languages/';
            $this->checkDirs();
            add_action('admin_menu', array($this, 'SLangSubmenu'),9);
            add_action('init', array($this, 'createPoFile'));
        }

        public function SLangSubmenu() {
            add_submenu_page('edit.php?post_type=' . $this->SL_langForPostType, 'Language Options', 'Language Options', 'manage_options', $this->SL_langForPostType . '_lang_option', array($this, 'displayLaguageOptions'));
        }

        public function displayLaguageOptions() {
            if ($_POST && isset($_POST['task']) && $_POST['task'] == 'lang-delete') {
                if (isset($_POST['lang-slug'])) {
                    $this->deleteTanslate($_POST['lang-slug']);
                }
                unset($_POST);
            }
            if (isset($_GET['lang-slug'])) {
                if (isset($_POST['data']) && is_array($_POST['data'])) {
                    $path = $this->SL_langDirPath . $this->SL_langPluginName . '-' . $_GET['lang-slug'] . '.po';
                    $this->replacePoData($path, $_POST['data']);
                }
                if(isset($_POST['synchron']) && $_POST['synchron'] == 'pot_synchron'){
                    $_POST['common-locale'] = $_GET['lang-slug'];
                    $this->createPoFile();
                }
                $data = $this->getPoData($this->SL_langPluginName . '-' . $_GET['lang-slug'] . '.po');
                $lang = $this->getLanguage($_GET['lang-slug']);
                if (isset($_POST['submit_button']) && $_POST['submit_button'] == 'Save') {
                    $url = 'edit.php?post_type=' . $_GET['post_type'] . '&page=' . $_GET['page'];
                    header('Location: ' . $url);
                    die;
                } else {
                    include('views/SLangViewPo.php');
                }
            } else {
                $translations = $this->getAllPoFiles($this->SL_langDirPath);
                include('views/SViewLangOptions.php');
            }
        }

        public function createPoFile() {
            if (isset($_GET['post_type']) && $_GET['post_type'] == $this->SL_langForPostType) {
                if (isset($_POST['common-locale']) && !empty($_POST['common-locale'])) {
                    $countries = $this->getCountries();
                    global $current_user;
                    $lang = $this->getLanguage($_POST['common-locale']);
                    $c = explode('_', $_POST['common-locale']);
                    $path = $this->SL_langDirPath . $this->SL_langPluginName . '-' . $_POST['common-locale'] . '.po';
                    if (!file_exists($path) || isset($_POST['synchron'])) {
                        if (file_exists($path) && isset($_POST['synchron']) && $_POST['synchron'] == 'pot_synchron') {
                            $prevPoData = $this->getPoData($this->SL_langPluginName . '-' . $_POST['common-locale'] . '.po');
                            unlink($path);
                        }
                        $potPath = $this->SL_langPluginDirPath . 'StaffDirectoryWD.pot';
                        $this->getAllPoFiles($this->SL_langDirPath);
                        $time = date('y-m-d h:s');
                        $offset = date('Z');
                        $po = '#, fuzzy' . "\n"
                                . 'msgid ""' . "\n"
                                . 'msgstr ""' . "\n"
                                . '"Project-Id-Version: ' . $this->SL_langPluginName . '\n"' . "\n"
                                . '"POT-Creation-Date: ' . $time . ($offset < 0 ? '-' : '+') . round($offset / 3600) . '00' . '\n"' . "\n"
                                . '"PO-Revision-Date: ' . $time . ($offset < 0 ? '-' : '+') . round($offset / 3600) . '00' . '\n"' . "\n"
                                . '"Last-Translator: ' . $current_user->data->user_login . ' < ' . $current_user->data->user_email . ' > \n"' . "\n"
                                . '"Language-Team: \n"' . "\n"
                                . '"Language: ' . $lang . '\n"' . "\n";
                        if (isset($countries['langs'][$c[0]])) {
                            $lang_arr = $countries['langs'][$c[0]];
                            $nplurals = (count($lang_arr[2]) > 2) ? count($lang_arr[2]) : '2';
                            $plural = ($lang_arr[1] != '') ? $lang_arr[1] : 'n!=1';
                            $po .='"Plural-Forms: nplurals=' . $nplurals . '; plural=' . $plural . '\n"' . "\n";
                        }
                        $po .='"MIME-Version: 1.0\n"' . "\n"
                                . '"Content-Type: text/plain; charset=UTF-8\n"' . "\n"
                                . '"Content-Transfer-Encoding: 8bit\n"' . "\n" . "\n";
                        $potFile = file_get_contents($potPath, FILE_USE_INCLUDE_PATH);
                        if ($potFile !== false) {
                            $potSubStr = substr($potFile, strpos($potFile, '#:'));
                        } else {
                            $_POST['lang_err_mess'] = 'Cannot read POT file .';
                        }
                        $po .= $potSubStr;
                        $res = $this->compileMoFile($path, $po);

                        $currentPoData = $this->getPoData(basename($path));
                        if(isset($prevPoData)){
                            foreach ($currentPoData as $key => $value) {
                                foreach ($prevPoData as $key1 => $value1) {
                                    if($value[0] == $value1[0]){
                                        $currentPoData[$key][1] = $value1[1];
                                    }
                                }
                            }
                        }
                        $dataTranslate = $this->defaultTranslate($currentPoData, $c[0]);
                        $this->replacePoData($path, $dataTranslate);
                        if (is_array($res)) {
                            $_POST['lang_success'] = array('path' => $path, 'lang' => $lang, 'filename' => $res['filename']);
                            $_POST['lang_success_synchron'] = "Data of PO file was successfully synchronized with POT.";
                        } else {
                            $_POST['lang_err_mess'] = $res;
                        }
                    } else {
                        $_POST['lang_err_mess'] = "File already exists.";
                    }
                }
            }
        }

        private function getPoData($poName) {
            $path = $this->SL_langDirPath . $poName;
            $data = array();
            $handle = fopen($path, "r");
            $can_create = '';
            $many_lines = false;
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'msgstr ') !== FALSE && $can_create != '') {
                        if (strpos($line, '"') != strrpos($line, '"') - 1)
                            $data[] = array($can_create, substr($line, strpos($line, '"') + 1, strrpos($line, '"') - strpos($line, '"') - 1));
                        else
                            $data[] = array($can_create, '');
                        $can_create = '';
                        $many_lines = false;
                    }elseif ($many_lines) {
                        $can_create .= substr($line, strpos($line, '"') + 1, strrpos($line, '"') - strpos($line, '"') - 1);
                    }
                    if (strpos($line, 'msgid ') !== FALSE) {
                        if (strpos($line, '"') != strrpos($line, '"') - 1)
                            $can_create = substr($line, strpos($line, '"') + 1, strrpos($line, '"') - strpos($line, '"') - 1);
                        $many_lines = true;
                    }
                }
                fclose($handle);
            } else {
                // error opening the file.
            }
            return $data;
        }

        private function replacePoData($path, $data) {
            $po = '';
            $handle = fopen($path, "r");
            $can_create = '';

            $many_lines = TRUE;
            $i = 0;
            $j=0;
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'msgstr ') !== FALSE) {
                        $j++;
                        if($j>1){
                            $line = 'msgstr "' . $can_create . '"' . PHP_EOL;
                            $can_create = '';
                            $i++;
                        }
                    } elseif (!$many_lines) {
                        $many_lines = TRUE;
                        $can_create = $data[$i][1];
                    }
                    if (strpos($line, 'msgid ') !== FALSE) {
                        if (strpos($line, '"') != strrpos($line, '"') - 1)
                            $can_create = $data[$i][1];
                        $many_lines = false;
                    }
                    $po .=$line;
                }

                fclose($handle);
            } else {
                $_POST['lang_err_mess'] = 'Failed to open PO file';
                return;
            }
            $res = $this->compileMoFile($path, $po);
            if (is_array($res)) {
                $_POST['lang_success'] = true;
            } else {
                $_POST['lang_err_mess'] = $res;
            }
        }

        private function defaultTranslate($data, $toLang) {
//            require_once 'GoogleTranslate.php';
//            $GT = new GoogleTranslate();
//            foreach ($data as $key => $value) {
//                if($value[1] == '') {
//                    $data[$key][1] = $GT->translateText($value[0], 'en', $toLang)->text;
//                }
//            }
            return $data;
        }

        private function compileMoFile($path, $po) {
            if (empty($path) || empty($po)) {
                $response = 'Invalid data posted to server';
                return $response;
            }
            $fname = basename($path);
            $podir = dirname($path);
            $dname = basename($podir);
            $ftype = 'PO';



// else construct directory tree if file does not exist
            if (!file_exists($podir) && !mkdir($path, 0775, true)) {
                $pname = basename(dirname($podir));
                $response = sprintf('Web server cannot create "%s" directory in "%s". Fix file permissions or create it manually.', $dname);
                return $response;
            } else if (!is_dir($podir) || !is_writable($podir)) {
                $response = sprintf('Web server cannot create files in the "%s" directory. Fix file permissions or use the download function.', $podir);

                return $response;
            }

// Undo magic quotes if enabled
            if (get_magic_quotes_gpc()) {
                $po = stripslashes($po);
            }

// attempt to write PO file
            $bytes = file_put_contents($path, $po);
            if (false === $bytes) {
                $response = sprintf('%s file is not writable by the web server. Fix file permissions or download and copy to "%s/%s".', $ftype, $dname);

                return $response;
            }

// primary action ok
            $response = array(
                'bytes' => $bytes,
                'filename' => basename($path)
            );

// attempt to write MO file also, but may fail for numerous reasons.
            try {
// check target MO path before compiling
                $mopath = preg_replace('/\.po$/', '.mo', $path);
                if (!file_exists($mopath) && !is_writable(dirname($mopath))) {
                    $response = 'Cannot create MO file';
                    return $response;
                } else if (file_exists($mopath) && !is_writable($mopath)) {
                    $response = 'Cannot overwrite MO file';
                    return $response;
                }

// Fall back to in-built MO compiler - requires PO is parsed too
                $mo = $this->msgfmt_native($po);
                $bytes = file_put_contents($mopath, $mo);
                if (!$bytes) {
                    $response = 'Failed to write MO file';
                    return $response;
                }
                $response['compiled'] = $bytes;
            } catch (Exception $e) {
                $response['compiled'] = $e->getMessage();
            }

            return $response;
        }

        private function getAllPoFiles($dir) {
            $files = glob($dir . "*.po");
            $filedirs = '';
            foreach ($files as $file) {
                $last_path = substr(basename($file), -8, 5);
                $country = (strpos($last_path, '-') == FALSE) ? $last_path : substr($last_path, -2);
                $filedirs[$country] = $this->getLanguage($country);
            }
            return $filedirs;
        }

        private function msgfmt_native($po) {
            try {
                require_once 'SLGettextCompiled.php';
                $mo = loco_msgfmt($po, FALSE);
            } catch (Exception $Ex) {
                error_log($Ex->getMessage(), 0);
            }
            if (!$mo) {
                echo 'Failed to compile MO file with built-in compiler';
            }
            return $mo;
        }

        private function getLanguage($country) {
            $countries = $this->getCountries();
            $c = explode('_', $country);
            if (count($c) == 2 && isset($countries['locales'][$c[0]][$c[1]])) {
                $lang = $countries['locales'][$c[0]][$c[1]];
                return $lang;
            } elseif(isset($countries['locales'][$c[0]][""])) {
                $lang = $countries['locales'][$c[0]][""];
                return $lang;
            }
        }

        private function deleteTanslate($lang_slug) {
            $po = $this->SL_langDirPath . $this->SL_langPluginName . '-' . $lang_slug . '.po';
            $mo = $this->SL_langDirPath . $this->SL_langPluginName . '-' . $lang_slug . '.mo';
            if (file_exists($mo)) {
                unlink($mo);
            }
            if (file_exists($po)) {
                unlink($po);
            }
        }

        private function checkDirs() {
            if (!file_exists($this->SL_langDirPath)) {
                mkdir($this->SL_langDirPath, 0777, true);
            }
        }

        private function getCountries() {
            return unserialize('a:3:{s:7:"locales";a:120:{s:2:"af";a:1:{s:0:"";s:9:"Afrikaans";}s:2:"ak";a:1:{s:0:"";s:4:"Akan";}s:2:"sq";a:1:{s:0:"";s:8:"Albanian";}s:2:"am";a:1:{s:0:"";s:7:"Amharic";}s:2:"ar";a:1:{s:0:"";s:6:"Arabic";}s:2:"hy";a:1:{s:0:"";s:8:"Armenian";}s:3:"rup";a:1:{s:2:"MK";s:9:"Aromanian";}s:2:"as";a:1:{s:0:"";s:8:"Assamese";}s:2:"az";a:2:{s:0:"";s:11:"Azerbaijani";s:2:"TR";s:20:"Azerbaijani (Turkey)";}s:2:"ba";a:1:{s:0:"";s:7:"Bashkir";}s:2:"eu";a:1:{s:0:"";s:6:"Basque";}s:3:"bel";a:1:{s:0:"";s:10:"Belarusian";}s:2:"bn";a:1:{s:2:"BD";s:7:"Bengali";}s:2:"bs";a:1:{s:2:"BA";s:7:"Bosnian";}s:2:"bg";a:1:{s:2:"BG";s:9:"Bulgarian";}s:2:"my";a:1:{s:2:"MM";s:7:"Burmese";}s:2:"ca";a:1:{s:0:"";s:7:"Catalan";}s:3:"bal";a:1:{s:0:"";s:16:"Catalan (Balear)";}s:2:"zh";a:3:{s:2:"CN";s:15:"Chinese (China)";s:2:"HK";s:19:"Chinese (Hong Kong)";s:2:"TW";s:16:"Chinese (Taiwan)";}s:2:"co";a:1:{s:0:"";s:8:"Corsican";}s:2:"hr";a:1:{s:0:"";s:8:"Croatian";}s:2:"cs";a:1:{s:2:"CZ";s:5:"Czech";}s:2:"da";a:1:{s:2:"DK";s:6:"Danish";}s:2:"dv";a:1:{s:0:"";s:7:"Dhivehi";}s:2:"nl";a:2:{s:2:"NL";s:5:"Dutch";s:2:"BE";s:15:"Dutch (Belgium)";}s:2:"en";a:4:{s:2:"US";s:7:"English";s:2:"AU";s:19:"English (Australia)";s:2:"CA";s:16:"English (Canada)";s:2:"GB";s:12:"English (UK)";}s:2:"eo";a:1:{s:0:"";s:9:"Esperanto";}s:2:"et";a:1:{s:0:"";s:8:"Estonian";}s:2:"fo";a:1:{s:0:"";s:7:"Faroese";}s:2:"fi";a:1:{s:0:"";s:7:"Finnish";}s:2:"fr";a:2:{s:2:"BE";s:16:"French (Belgium)";s:2:"FR";s:15:"French (France)";}s:2:"fy";a:1:{s:0:"";s:7:"Frisian";}s:3:"fuc";a:1:{s:0:"";s:5:"Fulah";}s:2:"gl";a:1:{s:2:"ES";s:8:"Galician";}s:2:"ka";a:1:{s:2:"GE";s:8:"Georgian";}s:2:"de";a:2:{s:2:"DE";s:6:"German";s:2:"CH";s:20:"German (Switzerland)";}s:2:"el";a:1:{s:0:"";s:5:"Greek";}s:2:"gn";a:1:{s:0:"";s:8:"Guaraní";}s:2:"gu";a:1:{s:2:"IN";s:8:"Gujarati";}s:3:"haw";a:1:{s:2:"US";s:8:"Hawaiian";}s:3:"haz";a:1:{s:0:"";s:8:"Hazaragi";}s:2:"he";a:1:{s:2:"IL";s:6:"Hebrew";}s:2:"hi";a:1:{s:2:"IN";s:5:"Hindi";}s:2:"hu";a:1:{s:2:"HU";s:9:"Hungarian";}s:2:"is";a:1:{s:2:"IS";s:9:"Icelandic";}s:3:"ido";a:1:{s:0:"";s:3:"Ido";}s:2:"id";a:1:{s:2:"ID";s:10:"Indonesian";}s:2:"ga";a:1:{s:0:"";s:5:"Irish";}s:2:"it";a:1:{s:2:"IT";s:7:"Italian";}s:2:"ja";a:1:{s:0:"";s:8:"Japanese";}s:2:"jv";a:1:{s:2:"ID";s:8:"Javanese";}s:2:"kn";a:1:{s:0:"";s:7:"Kannada";}s:2:"kk";a:1:{s:0:"";s:6:"Kazakh";}s:2:"km";a:1:{s:0:"";s:5:"Khmer";}s:3:"kin";a:1:{s:0:"";s:11:"Kinyarwanda";}s:2:"ky";a:1:{s:2:"KY";s:7:"Kirghiz";}s:2:"ko";a:1:{s:2:"KR";s:6:"Korean";}s:3:"ckb";a:1:{s:0:"";s:16:"Kurdish (Sorani)";}s:2:"lo";a:1:{s:0:"";s:3:"Lao";}s:2:"lv";a:1:{s:0:"";s:7:"Latvian";}s:2:"li";a:1:{s:0:"";s:10:"Limburgish";}s:3:"lin";a:1:{s:0:"";s:7:"Lingala";}s:2:"lt";a:1:{s:2:"LT";s:10:"Lithuanian";}s:2:"lb";a:1:{s:2:"LU";s:13:"Luxembourgish";}s:2:"mk";a:1:{s:2:"MK";s:10:"Macedonian";}s:2:"mg";a:1:{s:2:"MG";s:8:"Malagasy";}s:2:"ms";a:1:{s:2:"MY";s:5:"Malay";}s:2:"ml";a:1:{s:2:"IN";s:9:"Malayalam";}s:2:"mr";a:1:{s:0:"";s:7:"Marathi";}s:3:"xmf";a:1:{s:0:"";s:10:"Mingrelian";}s:2:"mn";a:1:{s:0:"";s:9:"Mongolian";}s:2:"me";a:1:{s:2:"ME";s:11:"Montenegrin";}s:2:"ne";a:1:{s:2:"NP";s:6:"Nepali";}s:2:"nb";a:1:{s:2:"NO";s:19:"Norwegian (Bokmål)";}s:2:"nn";a:1:{s:2:"NO";s:19:"Norwegian (Nynorsk)";}s:3:"ory";a:1:{s:0:"";s:5:"Oriya";}s:2:"os";a:1:{s:0:"";s:7:"Ossetic";}s:2:"ps";a:1:{s:0:"";s:6:"Pashto";}s:2:"fa";a:2:{s:2:"IR";s:7:"Persian";s:2:"AF";s:21:"Persian (Afghanistan)";}s:2:"pl";a:1:{s:2:"PL";s:6:"Polish";}s:2:"pt";a:2:{s:2:"BR";s:19:"Portuguese (Brazil)";s:2:"PT";s:21:"Portuguese (Portugal)";}s:2:"pa";a:1:{s:2:"IN";s:7:"Punjabi";}s:3:"rhg";a:1:{s:0:"";s:8:"Rohingya";}s:2:"ro";a:1:{s:2:"RO";s:8:"Romanian";}s:2:"ru";a:2:{s:2:"RU";s:7:"Russian";s:2:"UA";s:17:"Russian (Ukraine)";}s:3:"rue";a:1:{s:0:"";s:5:"Rusyn";}s:3:"sah";a:1:{s:0:"";s:5:"Sakha";}s:2:"sa";a:1:{s:2:"IN";s:8:"Sanskrit";}s:3:"srd";a:1:{s:0:"";s:9:"Sardinian";}s:2:"gd";a:1:{s:0:"";s:15:"Scottish Gaelic";}s:2:"sr";a:1:{s:2:"RS";s:7:"Serbian";}s:2:"sd";a:1:{s:2:"PK";s:6:"Sindhi";}s:2:"si";a:1:{s:2:"LK";s:7:"Sinhala";}s:2:"sk";a:1:{s:2:"SK";s:6:"Slovak";}s:2:"sl";a:1:{s:2:"SI";s:9:"Slovenian";}s:2:"so";a:1:{s:2:"SO";s:6:"Somali";}s:3:"azb";a:1:{s:0:"";s:17:"South Azerbaijani";}s:2:"es";a:8:{s:2:"AR";s:19:"Spanish (Argentina)";s:2:"CL";s:15:"Spanish (Chile)";s:2:"CO";s:18:"Spanish (Colombia)";s:2:"MX";s:16:"Spanish (Mexico)";s:2:"PE";s:14:"Spanish (Peru)";s:2:"PR";s:21:"Spanish (Puerto Rico)";s:2:"ES";s:15:"Spanish (Spain)";s:2:"VE";s:19:"Spanish (Venezuela)";}s:2:"su";a:1:{s:2:"ID";s:9:"Sundanese";}s:2:"sw";a:1:{s:0:"";s:7:"Swahili";}s:2:"sv";a:1:{s:2:"SE";s:7:"Swedish";}s:3:"gsw";a:1:{s:0:"";s:12:"Swiss German";}s:2:"tl";a:1:{s:0:"";s:7:"Tagalog";}s:2:"tg";a:1:{s:0:"";s:5:"Tajik";}s:3:"tzm";a:1:{s:0:"";s:25:"Tamazight (Central Atlas)";}s:2:"ta";a:2:{s:2:"IN";s:5:"Tamil";s:2:"LK";s:17:"Tamil (Sri Lanka)";}s:2:"tt";a:1:{s:2:"RU";s:5:"Tatar";}s:2:"te";a:1:{s:0:"";s:6:"Telugu";}s:2:"th";a:1:{s:0:"";s:4:"Thai";}s:2:"bo";a:1:{s:0:"";s:7:"Tibetan";}s:3:"tir";a:1:{s:0:"";s:8:"Tigrinya";}s:2:"tr";a:1:{s:2:"TR";s:7:"Turkish";}s:3:"tuk";a:1:{s:0:"";s:7:"Turkmen";}s:2:"ug";a:1:{s:2:"CN";s:6:"Uighur";}s:2:"uk";a:1:{s:0:"";s:9:"Ukrainian";}s:2:"ur";a:1:{s:0:"";s:4:"Urdu";}s:2:"uz";a:1:{s:2:"UZ";s:5:"Uzbek";}s:2:"vi";a:1:{s:0:"";s:10:"Vietnamese";}s:2:"wa";a:1:{s:0:"";s:7:"Walloon";}s:2:"cy";a:1:{s:0:"";s:5:"Welsh";}}s:5:"langs";a:190:{s:2:"ab";a:3:{i:0;s:9:"Abkhazian";i:1;s:0:"";i:2;a:0:{}}s:2:"aa";a:3:{i:0;s:4:"Afar";i:1;s:0:"";i:2;a:0:{}}s:2:"af";a:3:{i:0;s:9:"Afrikaans";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ak";a:3:{i:0;s:4:"Akan";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"sq";a:3:{i:0;s:8:"Albanian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:3:"gsw";a:3:{i:0;s:21:"Alemani; Swiss German";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"am";a:3:{i:0;s:7:"Amharic";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ar";a:3:{i:0;s:6:"Arabic";i:1;s:95:"n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100 >= 3 && n%100<=10 ? 3 : n%100 >= 11 && n%100<=99 ? 4 : 5";i:2;a:6:{i:0;s:4:"zero";i:1;s:3:"one";i:2;s:3:"two";i:3;s:3:"few";i:4;s:4:"many";i:5;s:5:"other";}}s:2:"an";a:3:{i:0;s:9:"Aragonese";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"hy";a:3:{i:0;s:8:"Armenian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:3:"rup";a:3:{i:0;s:37:"Aromanian; Arumanian; Macedo-Romanian";i:1;s:0:"";i:2;a:0:{}}s:2:"as";a:3:{i:0;s:8:"Assamese";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"av";a:3:{i:0;s:6:"Avaric";i:1;s:0:"";i:2;a:0:{}}s:2:"ae";a:3:{i:0;s:7:"Avestan";i:1;s:0:"";i:2;a:0:{}}s:2:"ay";a:3:{i:0;s:6:"Aymara";i:1;s:0:"";i:2;a:0:{}}s:2:"az";a:3:{i:0;s:11:"Azerbaijani";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:3:"bal";a:3:{i:0;s:7:"Baluchi";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"bm";a:3:{i:0;s:7:"Bambara";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ba";a:3:{i:0;s:7:"Bashkir";i:1;s:0:"";i:2;a:0:{}}s:2:"eu";a:3:{i:0;s:6:"Basque";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"be";a:3:{i:0;s:10:"Belarusian";i:1;s:82:"(n%10==1 && n%100!=11 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:4:"many";}}s:2:"bn";a:3:{i:0;s:7:"Bengali";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"bh";a:3:{i:0;s:6:"Bihari";i:1;s:28:"( n >= 0 && n <= 1 ) ? 0 : 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"bi";a:3:{i:0;s:7:"Bislama";i:1;s:0:"";i:2;a:0:{}}s:2:"bs";a:3:{i:0;s:7:"Bosnian";i:1;s:82:"(n%10==1 && n%100!=11 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"br";a:3:{i:0;s:6:"Breton";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"bg";a:3:{i:0;s:9:"Bulgarian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"my";a:3:{i:0;s:7:"Burmese";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ca";a:3:{i:0;s:18:"Catalan; Valencian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ch";a:3:{i:0;s:8:"Chamorro";i:1;s:0:"";i:2;a:0:{}}s:2:"ce";a:3:{i:0;s:7:"Chechen";i:1;s:0:"";i:2;a:0:{}}s:2:"ny";a:3:{i:0;s:23:"Chichewa; Chewa; Nyanja";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"zh";a:3:{i:0;s:7:"Chinese";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"cu";a:3:{i:0;s:64:"Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old";i:1;s:0:"";i:2;a:0:{}}s:2:"cv";a:3:{i:0;s:7:"Chuvash";i:1;s:0:"";i:2;a:0:{}}s:2:"kw";a:3:{i:0;s:7:"Cornish";i:1;s:27:"n == 1 ? 0 : n == 2 ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"two";i:2;s:5:"other";}}s:2:"co";a:3:{i:0;s:8:"Corsican";i:1;s:0:"";i:2;a:0:{}}s:2:"cr";a:3:{i:0;s:4:"Cree";i:1;s:0:"";i:2;a:0:{}}s:2:"hr";a:3:{i:0;s:8:"Croatian";i:1;s:80:"n%10==1 && n%100!=11 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"cs";a:3:{i:0;s:5:"Czech";i:1;s:45:"( n == 1 ) ? 0 : ( n >= 2 && n <= 4 ) ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"da";a:3:{i:0;s:6:"Danish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"dv";a:3:{i:0;s:26:"Divehi; Dhivehi; Maldivian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"nl";a:3:{i:0;s:14:"Dutch; Flemish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"dz";a:3:{i:0;s:8:"Dzongkha";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"en";a:3:{i:0;s:7:"English";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"eo";a:3:{i:0;s:9:"Esperanto";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"et";a:3:{i:0;s:8:"Estonian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ee";a:3:{i:0;s:3:"Ewe";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"fo";a:3:{i:0;s:7:"Faroese";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"fj";a:3:{i:0;s:6:"Fijian";i:1;s:0:"";i:2;a:0:{}}s:2:"fi";a:3:{i:0;s:7:"Finnish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"fr";a:3:{i:0;s:6:"French";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ff";a:3:{i:0;s:5:"Fulah";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"gd";a:3:{i:0;s:23:"Gaelic; Scottish Gaelic";i:1;s:26:"n < 2 ? 0 : n == 2 ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"two";i:2;s:5:"other";}}s:2:"gl";a:3:{i:0;s:8:"Galician";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"lg";a:3:{i:0;s:5:"Ganda";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ka";a:3:{i:0;s:8:"Georgian";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"de";a:3:{i:0;s:6:"German";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"el";a:3:{i:0;s:5:"Greek";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"gn";a:3:{i:0;s:7:"Guarani";i:1;s:0:"";i:2;a:0:{}}s:2:"gu";a:3:{i:0;s:8:"Gujarati";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ht";a:3:{i:0;s:23:"Haitian; Haitian Creole";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ha";a:3:{i:0;s:5:"Hausa";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:3:"haw";a:3:{i:0;s:8:"Hawaiian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"he";a:3:{i:0;s:6:"Hebrew";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"hz";a:3:{i:0;s:6:"Herero";i:1;s:0:"";i:2;a:0:{}}s:2:"hi";a:3:{i:0;s:5:"Hindi";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ho";a:3:{i:0;s:9:"Hiri Motu";i:1;s:0:"";i:2;a:0:{}}s:2:"hu";a:3:{i:0;s:9:"Hungarian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"is";a:3:{i:0;s:9:"Icelandic";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"io";a:3:{i:0;s:3:"Ido";i:1;s:0:"";i:2;a:0:{}}s:2:"ig";a:3:{i:0;s:4:"Igbo";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"id";a:3:{i:0;s:10:"Indonesian";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ia";a:3:{i:0;s:58:"Interlingua (International Auxiliary Language Association)";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ie";a:3:{i:0;s:11:"Interlingue";i:1;s:0:"";i:2;a:0:{}}s:2:"iu";a:3:{i:0;s:9:"Inuktitut";i:1;s:27:"n == 1 ? 0 : n == 2 ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"two";i:2;s:5:"other";}}s:2:"ik";a:3:{i:0;s:7:"Inupiaq";i:1;s:0:"";i:2;a:0:{}}s:2:"ga";a:3:{i:0;s:5:"Irish";i:1;s:44:"n==1 ? 0 : n==2 ? 1 : n<7 ? 2 : n<11 ? 3 : 4";i:2;a:5:{i:0;s:3:"one";i:1;s:3:"two";i:2;s:3:"few";i:3;s:4:"many";i:4;s:5:"other";}}s:2:"it";a:3:{i:0;s:7:"Italian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ja";a:3:{i:0;s:8:"Japanese";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"jv";a:3:{i:0;s:8:"Javanese";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"kl";a:3:{i:0;s:24:"Kalaallisut; Greenlandic";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"kn";a:3:{i:0;s:7:"Kannada";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"kr";a:3:{i:0;s:6:"Kanuri";i:1;s:0:"";i:2;a:0:{}}s:2:"ks";a:3:{i:0;s:8:"Kashmiri";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"kk";a:3:{i:0;s:6:"Kazakh";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"km";a:3:{i:0;s:5:"Khmer";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ki";a:3:{i:0;s:14:"Kikuyu; Gikuyu";i:1;s:0:"";i:2;a:0:{}}s:2:"rw";a:3:{i:0;s:11:"Kinyarwanda";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ky";a:3:{i:0;s:7:"Kirghiz";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"kv";a:3:{i:0;s:4:"Komi";i:1;s:0:"";i:2;a:0:{}}s:2:"kg";a:3:{i:0;s:5:"Kongo";i:1;s:0:"";i:2;a:0:{}}s:2:"ko";a:3:{i:0;s:6:"Korean";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"kj";a:3:{i:0;s:18:"Kuanyama; Kwanyama";i:1;s:0:"";i:2;a:0:{}}s:2:"ku";a:3:{i:0;s:7:"Kurdish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"lo";a:3:{i:0;s:3:"Lao";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"la";a:3:{i:0;s:5:"Latin";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"lv";a:3:{i:0;s:7:"Latvian";i:1;s:49:"n % 10 == 1 && n % 100 != 11 ? 0 : n != 0 ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:5:"other";i:2;s:4:"zero";}}s:2:"li";a:3:{i:0;s:32:"Limburgan; Limburger; Limburgish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ln";a:3:{i:0;s:7:"Lingala";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"lt";a:3:{i:0;s:10:"Lithuanian";i:1;s:71:"(n%10==1 && n%100!=11 ? 0 : n%10 >= 2 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"lu";a:3:{i:0;s:12:"Luba-Katanga";i:1;s:0:"";i:2;a:0:{}}s:2:"lb";a:3:{i:0;s:28:"Luxembourgish; Letzeburgesch";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"mk";a:3:{i:0;s:10:"Macedonian";i:1;s:40:"( n % 10 == 1 && n % 100 != 11 ) ? 0 : 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"mg";a:3:{i:0;s:8:"Malagasy";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ms";a:3:{i:0;s:5:"Malay";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ml";a:3:{i:0;s:9:"Malayalam";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"mt";a:3:{i:0;s:7:"Maltese";i:1;s:75:"(n==1 ? 0 : n==0||( n%100>1 && n%100<11)? 1 :(n%100>10 && n%100<20)? 2 : 3)";i:2;a:4:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:4:"many";i:3;s:5:"other";}}s:2:"gv";a:3:{i:0;s:4:"Manx";i:1;s:43:"n%10==1 ? 0 : n%10==2 ? 1 : n%20==0 ? 2 : 3";i:2;a:4:{i:0;s:3:"one";i:1;s:3:"two";i:2;s:3:"few";i:3;s:5:"other";}}s:2:"mi";a:3:{i:0;s:5:"Maori";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"mr";a:3:{i:0;s:7:"Marathi";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"mh";a:3:{i:0;s:11:"Marshallese";i:1;s:0:"";i:2;a:0:{}}s:2:"mo";a:3:{i:0;s:9:"Moldavian";i:1;s:50:"n == 1 ? 0 : n % 100 >= 1 && n % 100 <= 19 ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"mn";a:3:{i:0;s:9:"Mongolian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"na";a:3:{i:0;s:5:"Nauru";i:1;s:0:"";i:2;a:0:{}}s:2:"nv";a:3:{i:0;s:14:"Navajo; Navaho";i:1;s:0:"";i:2;a:0:{}}s:2:"nd";a:3:{i:0;s:29:"Ndebele, North; North Ndebele";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"nr";a:3:{i:0;s:29:"Ndebele, South; South Ndebele";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ng";a:3:{i:0;s:6:"Ndonga";i:1;s:0:"";i:2;a:0:{}}s:2:"ne";a:3:{i:0;s:6:"Nepali";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"se";a:3:{i:0;s:13:"Northern Sami";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"no";a:3:{i:0;s:9:"Norwegian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"nb";a:3:{i:0;s:17:"Norwegian Bokmål";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"nn";a:3:{i:0;s:17:"Norwegian Nynorsk";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"oc";a:3:{i:0;s:31:"Occitan (post 1500); Provençal";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"oj";a:3:{i:0;s:6:"Ojibwa";i:1;s:0:"";i:2;a:0:{}}s:2:"or";a:3:{i:0;s:5:"Oriya";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"om";a:3:{i:0;s:5:"Oromo";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"os";a:3:{i:0;s:17:"Ossetian; Ossetic";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"pi";a:3:{i:0;s:4:"Pali";i:1;s:0:"";i:2;a:0:{}}s:2:"pa";a:3:{i:0;s:16:"Panjabi; Punjabi";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"fa";a:3:{i:0;s:7:"Persian";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"pl";a:3:{i:0;s:6:"Polish";i:1;s:66:"(n==1 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:4:"many";}}s:2:"pt";a:3:{i:0;s:10:"Portuguese";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ps";a:3:{i:0;s:6:"Pushto";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"qu";a:3:{i:0;s:7:"Quechua";i:1;s:0:"";i:2;a:0:{}}s:2:"rm";a:3:{i:0;s:13:"Raeto-Romance";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ro";a:3:{i:0;s:8:"Romanian";i:1;s:56:"(n==1 ? 0 :(((n%100>19)||(( n%100==0)&&(n!=0)))? 2 : 1))";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"rn";a:3:{i:0;s:5:"Rundi";i:1;s:0:"";i:2;a:0:{}}s:2:"ru";a:3:{i:0;s:7:"Russian";i:1;s:82:"(n%10==1 && n%100!=11 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:4:"many";}}s:2:"sm";a:3:{i:0;s:6:"Samoan";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"sg";a:3:{i:0;s:5:"Sango";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"sa";a:3:{i:0;s:8:"Sanskrit";i:1;s:0:"";i:2;a:0:{}}s:2:"sc";a:3:{i:0;s:9:"Sardinian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"sr";a:3:{i:0;s:7:"Serbian";i:1;s:82:"(n%10==1 && n%100!=11 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"sn";a:3:{i:0;s:5:"Shona";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ii";a:3:{i:0;s:10:"Sichuan Yi";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"sd";a:3:{i:0;s:6:"Sindhi";i:1;s:0:"";i:2;a:0:{}}s:2:"si";a:3:{i:0;s:18:"Sinhala; Sinhalese";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"sk";a:3:{i:0;s:6:"Slovak";i:1;s:45:"( n == 1 ) ? 0 : ( n >= 2 && n <= 4 ) ? 1 : 2";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:5:"other";}}s:2:"sl";a:3:{i:0;s:9:"Slovenian";i:1;s:56:"n%100==1 ? 0 : n%100==2 ? 1 : n%100==3||n%100==4 ? 2 : 3";i:2;a:4:{i:0;s:3:"one";i:1;s:3:"two";i:2;s:3:"few";i:3;s:5:"other";}}s:2:"so";a:3:{i:0;s:6:"Somali";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"st";a:3:{i:0;s:15:"Sotho, Southern";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"es";a:3:{i:0;s:7:"Spanish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"su";a:3:{i:0;s:9:"Sundanese";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"sw";a:3:{i:0;s:7:"Swahili";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ss";a:3:{i:0;s:5:"Swati";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"sv";a:3:{i:0;s:7:"Swedish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"tl";a:3:{i:0;s:7:"Tagalog";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ty";a:3:{i:0;s:8:"Tahitian";i:1;s:0:"";i:2;a:0:{}}s:2:"tg";a:3:{i:0;s:5:"Tajik";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ta";a:3:{i:0;s:5:"Tamil";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"tt";a:3:{i:0;s:5:"Tatar";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"te";a:3:{i:0;s:6:"Telugu";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"th";a:3:{i:0;s:4:"Thai";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"bo";a:3:{i:0;s:7:"Tibetan";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ti";a:3:{i:0;s:8:"Tigrinya";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"to";a:3:{i:0;s:21:"Tonga (Tonga Islands)";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"ts";a:3:{i:0;s:6:"Tsonga";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"tn";a:3:{i:0;s:6:"Tswana";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"tr";a:3:{i:0;s:7:"Turkish";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"tk";a:3:{i:0;s:7:"Turkmen";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"tw";a:3:{i:0;s:3:"Twi";i:1;s:0:"";i:2;a:0:{}}s:2:"ug";a:3:{i:0;s:14:"Uighur; Uyghur";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"uk";a:3:{i:0;s:9:"Ukrainian";i:1;s:82:"(n%10==1 && n%100!=11 ? 0 : n%10 >= 2 && n%10<=4 &&(n%100<10||n%100 >= 20)? 1 : 2)";i:2;a:3:{i:0;s:3:"one";i:1;s:3:"few";i:2;s:4:"many";}}s:2:"ur";a:3:{i:0;s:4:"Urdu";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"uz";a:3:{i:0;s:5:"Uzbek";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"ve";a:3:{i:0;s:5:"Venda";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"vi";a:3:{i:0;s:10:"Vietnamese";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"vo";a:3:{i:0;s:8:"Volapük";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"wa";a:3:{i:0;s:7:"Walloon";i:1;s:5:"n > 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"cy";a:3:{i:0;s:5:"Welsh";i:1;s:56:"n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n==3 ? 3 : n==6 ? 4 : 5";i:2;a:6:{i:0;s:4:"zero";i:1;s:3:"one";i:2;s:3:"two";i:3;s:3:"few";i:4;s:4:"many";i:5;s:5:"other";}}s:2:"fy";a:3:{i:0;s:15:"Western Frisian";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"wo";a:3:{i:0;s:5:"Wolof";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"xh";a:3:{i:0;s:5:"Xhosa";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:3:"sah";a:3:{i:0;s:5:"Yakut";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"yi";a:3:{i:0;s:7:"Yiddish";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}s:2:"yo";a:3:{i:0;s:6:"Yoruba";i:1;s:1:"0";i:2;a:1:{i:0;s:5:"other";}}s:2:"za";a:3:{i:0;s:14:"Zhuang; Chuang";i:1;s:0:"";i:2;a:0:{}}s:2:"zu";a:3:{i:0;s:4:"Zulu";i:1;s:6:"n != 1";i:2;a:2:{i:0;s:3:"one";i:1;s:5:"other";}}}s:7:"regions";a:249:{s:2:"AF";s:11:"Afghanistan";s:2:"AX";s:14:"Åland Islands";s:2:"AL";s:7:"Albania";s:2:"DZ";s:7:"Algeria";s:2:"AS";s:14:"American Samoa";s:2:"AD";s:7:"Andorra";s:2:"AO";s:6:"Angola";s:2:"AI";s:8:"Anguilla";s:2:"AQ";s:10:"Antarctica";s:2:"AG";s:19:"Antigua and Barbuda";s:2:"AR";s:9:"Argentina";s:2:"AM";s:7:"Armenia";s:2:"AW";s:5:"Aruba";s:2:"AU";s:9:"Australia";s:2:"AT";s:7:"Austria";s:2:"AZ";s:10:"Azerbaijan";s:2:"BS";s:7:"Bahamas";s:2:"BH";s:7:"Bahrain";s:2:"BD";s:10:"Bangladesh";s:2:"BB";s:8:"Barbados";s:2:"BY";s:7:"Belarus";s:2:"BE";s:7:"Belgium";s:2:"BZ";s:6:"Belize";s:2:"BJ";s:5:"Benin";s:2:"BM";s:7:"Bermuda";s:2:"BT";s:6:"Bhutan";s:2:"BO";s:31:"Bolivia, Plurinational State of";s:2:"BQ";s:32:"Bonaire, Sint Eustatius and Saba";s:2:"BA";s:22:"Bosnia and Herzegovina";s:2:"BW";s:8:"Botswana";s:2:"BV";s:13:"Bouvet Island";s:2:"BR";s:6:"Brazil";s:2:"IO";s:30:"British Indian Ocean Territory";s:2:"BN";s:17:"Brunei Darussalam";s:2:"BG";s:8:"Bulgaria";s:2:"BF";s:12:"Burkina Faso";s:2:"BI";s:7:"Burundi";s:2:"KH";s:8:"Cambodia";s:2:"CM";s:8:"Cameroon";s:2:"CA";s:6:"Canada";s:2:"CV";s:10:"Cape Verde";s:2:"KY";s:14:"Cayman Islands";s:2:"CF";s:24:"Central African Republic";s:2:"TD";s:4:"Chad";s:2:"CL";s:5:"Chile";s:2:"CN";s:5:"China";s:2:"CX";s:16:"Christmas Island";s:2:"CC";s:23:"Cocos (Keeling) Islands";s:2:"CO";s:8:"Colombia";s:2:"KM";s:7:"Comoros";s:2:"CG";s:5:"Congo";s:2:"CD";s:37:"Congo, The Democratic Republic of The";s:2:"CK";s:12:"Cook Islands";s:2:"CR";s:10:"Costa Rica";s:2:"CI";s:14:"Côte D\'Ivoire";s:2:"HR";s:7:"Croatia";s:2:"CU";s:4:"Cuba";s:2:"CW";s:8:"Curaçao";s:2:"CY";s:6:"Cyprus";s:2:"CZ";s:14:"Czech Republic";s:2:"DK";s:7:"Denmark";s:2:"DJ";s:8:"Djibouti";s:2:"DM";s:8:"Dominica";s:2:"DO";s:18:"Dominican Republic";s:2:"EC";s:7:"Ecuador";s:2:"EG";s:5:"Egypt";s:2:"SV";s:11:"El Salvador";s:2:"GQ";s:17:"Equatorial Guinea";s:2:"ER";s:7:"Eritrea";s:2:"EE";s:7:"Estonia";s:2:"ET";s:8:"Ethiopia";s:2:"FK";s:27:"Falkland Islands (Malvinas)";s:2:"FO";s:13:"Faroe Islands";s:2:"FJ";s:4:"Fiji";s:2:"FI";s:7:"Finland";s:2:"FR";s:6:"France";s:2:"GF";s:13:"French Guiana";s:2:"PF";s:16:"French Polynesia";s:2:"TF";s:27:"French Southern Territories";s:2:"GA";s:5:"Gabon";s:2:"GM";s:6:"Gambia";s:2:"GE";s:7:"Georgia";s:2:"DE";s:7:"Germany";s:2:"GH";s:5:"Ghana";s:2:"GI";s:9:"Gibraltar";s:2:"GR";s:6:"Greece";s:2:"GL";s:9:"Greenland";s:2:"GD";s:7:"Grenada";s:2:"GP";s:10:"Guadeloupe";s:2:"GU";s:4:"Guam";s:2:"GT";s:9:"Guatemala";s:2:"GG";s:8:"Guernsey";s:2:"GN";s:6:"Guinea";s:2:"GW";s:13:"Guinea-Bissau";s:2:"GY";s:6:"Guyana";s:2:"HT";s:5:"Haiti";s:2:"HM";s:33:"Heard Island and Mcdonald Islands";s:2:"VA";s:29:"Holy See (Vatican City State)";s:2:"HN";s:8:"Honduras";s:2:"HK";s:9:"Hong Kong";s:2:"HU";s:7:"Hungary";s:2:"IS";s:7:"Iceland";s:2:"IN";s:5:"India";s:2:"ID";s:9:"Indonesia";s:2:"IR";s:25:"Iran, Islamic Republic of";s:2:"IQ";s:4:"Iraq";s:2:"IE";s:7:"Ireland";s:2:"IM";s:11:"Isle of Man";s:2:"IL";s:6:"Israel";s:2:"IT";s:5:"Italy";s:2:"JM";s:7:"Jamaica";s:2:"JP";s:5:"Japan";s:2:"JE";s:6:"Jersey";s:2:"JO";s:6:"Jordan";s:2:"KZ";s:10:"Kazakhstan";s:2:"KE";s:5:"Kenya";s:2:"KI";s:8:"Kiribati";s:2:"KP";s:38:"Korea, Democratic People\'s Republic of";s:2:"KR";s:18:"Korea, Republic of";s:2:"KW";s:6:"Kuwait";s:2:"KG";s:10:"Kyrgyzstan";s:2:"LA";s:32:"Lao People\'s Democratic Republic";s:2:"LV";s:6:"Latvia";s:2:"LB";s:7:"Lebanon";s:2:"LS";s:7:"Lesotho";s:2:"LR";s:7:"Liberia";s:2:"LY";s:5:"Libya";s:2:"LI";s:13:"Liechtenstein";s:2:"LT";s:9:"Lithuania";s:2:"LU";s:10:"Luxembourg";s:2:"MO";s:5:"Macao";s:2:"MK";s:42:"Macedonia, The Former Yugoslav Republic of";s:2:"MG";s:10:"Madagascar";s:2:"MW";s:6:"Malawi";s:2:"MY";s:8:"Malaysia";s:2:"MV";s:8:"Maldives";s:2:"ML";s:4:"Mali";s:2:"MT";s:5:"Malta";s:2:"MH";s:16:"Marshall Islands";s:2:"MQ";s:10:"Martinique";s:2:"MR";s:10:"Mauritania";s:2:"MU";s:9:"Mauritius";s:2:"YT";s:7:"Mayotte";s:2:"MX";s:6:"Mexico";s:2:"FM";s:31:"Micronesia, Federated States of";s:2:"MD";s:20:"Moldova, Republic of";s:2:"MC";s:6:"Monaco";s:2:"MN";s:8:"Mongolia";s:2:"ME";s:10:"Montenegro";s:2:"MS";s:10:"Montserrat";s:2:"MA";s:7:"Morocco";s:2:"MZ";s:10:"Mozambique";s:2:"MM";s:7:"Myanmar";s:2:"NA";s:7:"Namibia";s:2:"NR";s:5:"Nauru";s:2:"NP";s:5:"Nepal";s:2:"NL";s:11:"Netherlands";s:2:"NC";s:13:"New Caledonia";s:2:"NZ";s:11:"New Zealand";s:2:"NI";s:9:"Nicaragua";s:2:"NE";s:5:"Niger";s:2:"NG";s:7:"Nigeria";s:2:"NU";s:4:"Niue";s:2:"NF";s:14:"Norfolk Island";s:2:"MP";s:24:"Northern Mariana Islands";s:2:"NO";s:6:"Norway";s:2:"OM";s:4:"Oman";s:2:"PK";s:8:"Pakistan";s:2:"PW";s:5:"Palau";s:2:"PS";s:31:"Palestinian Territory, Occupied";s:2:"PA";s:6:"Panama";s:2:"PG";s:16:"Papua New Guinea";s:2:"PY";s:8:"Paraguay";s:2:"PE";s:4:"Peru";s:2:"PH";s:11:"Philippines";s:2:"PN";s:8:"Pitcairn";s:2:"PL";s:6:"Poland";s:2:"PT";s:8:"Portugal";s:2:"PR";s:11:"Puerto Rico";s:2:"QA";s:5:"Qatar";s:2:"RE";s:8:"Réunion";s:2:"RO";s:7:"Romania";s:2:"RU";s:18:"Russian Federation";s:2:"RW";s:6:"Rwanda";s:2:"BL";s:17:"Saint Barthélemy";s:2:"SH";s:44:"Saint Helena, Ascension and Tristan Da Cunha";s:2:"KN";s:21:"Saint Kitts and Nevis";s:2:"LC";s:11:"Saint Lucia";s:2:"MF";s:26:"Saint Martin (French Part)";s:2:"PM";s:25:"Saint Pierre and Miquelon";s:2:"VC";s:32:"Saint Vincent and The Grenadines";s:2:"WS";s:5:"Samoa";s:2:"SM";s:10:"San Marino";s:2:"ST";s:21:"Sao Tome and Principe";s:2:"SA";s:12:"Saudi Arabia";s:2:"SN";s:7:"Senegal";s:2:"RS";s:6:"Serbia";s:2:"SC";s:10:"Seychelles";s:2:"SL";s:12:"Sierra Leone";s:2:"SG";s:9:"Singapore";s:2:"SX";s:25:"Sint Maarten (Dutch Part)";s:2:"SK";s:8:"Slovakia";s:2:"SI";s:8:"Slovenia";s:2:"SB";s:15:"Solomon Islands";s:2:"SO";s:7:"Somalia";s:2:"ZA";s:12:"South Africa";s:2:"GS";s:44:"South Georgia and The South Sandwich Islands";s:2:"SS";s:11:"South Sudan";s:2:"ES";s:5:"Spain";s:2:"LK";s:9:"Sri Lanka";s:2:"SD";s:5:"Sudan";s:2:"SR";s:8:"Suriname";s:2:"SJ";s:22:"Svalbard and Jan Mayen";s:2:"SZ";s:9:"Swaziland";s:2:"SE";s:6:"Sweden";s:2:"CH";s:11:"Switzerland";s:2:"SY";s:20:"Syrian Arab Republic";s:2:"TW";s:25:"Taiwan, Province of China";s:2:"TJ";s:10:"Tajikistan";s:2:"TZ";s:28:"Tanzania, United Republic of";s:2:"TH";s:8:"Thailand";s:2:"TL";s:11:"Timor-Leste";s:2:"TG";s:4:"Togo";s:2:"TK";s:7:"Tokelau";s:2:"TO";s:5:"Tonga";s:2:"TT";s:19:"Trinidad and Tobago";s:2:"TN";s:7:"Tunisia";s:2:"TR";s:6:"Turkey";s:2:"TM";s:12:"Turkmenistan";s:2:"TC";s:24:"Turks and Caicos Islands";s:2:"TV";s:6:"Tuvalu";s:2:"UG";s:6:"Uganda";s:2:"UA";s:7:"Ukraine";s:2:"AE";s:20:"United Arab Emirates";s:2:"GB";s:14:"United Kingdom";s:2:"US";s:13:"United States";s:2:"UM";s:36:"United States Minor Outlying Islands";s:2:"UY";s:7:"Uruguay";s:2:"UZ";s:10:"Uzbekistan";s:2:"VU";s:7:"Vanuatu";s:2:"VE";s:33:"Venezuela, Bolivarian Republic of";s:2:"VN";s:8:"Viet Nam";s:2:"VG";s:23:"Virgin Islands, British";s:2:"VI";s:20:"Virgin Islands, U.S.";s:2:"WF";s:17:"Wallis and Futuna";s:2:"EH";s:14:"Western Sahara";s:2:"YE";s:5:"Yemen";s:2:"ZM";s:6:"Zambia";s:2:"ZW";s:8:"Zimbabwe";}}');
            ;
        }

    }

}
