<?php

    namespace Wow\Template;

    use Exception;
    use Wow;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;

    /**
     * Class View
     *
     * The View class represents output to be displayed. It provides
     * methods for managing view data and inserts the data into
     * view templates upon rendering.
     *
     * @package Wow\Template
     */
    class View {

        /**
         * Request
         *
         * @var Request
         */
        protected $request;


        /**
         * Route
         *
         * @var Route
         */
        protected $route;

        /**
         * Response
         *
         * @var Response
         */
        protected $response;


        /**
         * HTML
         *
         * @var Html
         */
        protected $html;


        /**
         * View variables.
         *
         * @var array
         */
        protected $vars = array();

        /**
         * View sections.
         *
         * @var array
         */
        protected $sections = array();
        /**
         * Languages
         *
         * @var array
         */

        /**
         * Theme
         *
         * @var string
         */
        protected $theme = "default";

        protected $languages = array();
        /**
         * Active Language
         *
         * @var string
         */
        protected $selectedLanguage = "en";

        /**
         * Selected section to prepend or append content.
         *
         * @var string
         */
        protected $selectedSection = NULL;

        /**
         * View Body
         *
         * @var string
         */
        protected $body = NULL;

        /**
         * Master Template
         *
         * @var string
         */
        protected $layout = NULL;


        /**
         * Template file.
         *
         * @var string
         */
        private $template;

        /**
         * View constructor.
         *
         * @param Request $request
         */
        public function __construct(Request $request, Route $route = NULL) {
            $this->request  = $request;
            $this->response = new Response();
            $this->html     = new Html($request, $this->response);
            $this->route    = $route;
            $this->setTheme(Wow::get('app/theme'));
            $this->setLayout(Wow::get('app/layout'));
            $this->setLanguage(Wow::get('app/language'));
        }


        /**
         * Gets a Template variable.
         *
         * @param string $key Key
         *
         * @return mixed Value
         */
        public function get($key) {
            return isset($this->vars[$key]) ? $this->vars[$key] : NULL;
        }

        /**
         * Sets a Template variable.
         *
         * @param mixed  $key   Key
         * @param string $value Value
         */
        public function set($key, $value = NULL) {
            if(is_array($key) || is_object($key)) {
                foreach($key as $k => $v) {
                    $this->vars[$k] = $v;
                }
            } else {
                $this->vars[$key] = $value;
            }
        }

        /**
         * Checks if a Template variable is set.
         *
         * @param string $key Key
         *
         * @return boolean If key exists
         */
        public function has($key) {
            return isset($this->vars[$key]);
        }

        /**
         * Unsets a Template variable. If no key is passed in, clear all variables.
         *
         * @param string $key Key
         */
        public function clear($key = NULL) {
            if(is_null($key)) {
                $this->vars = array();
            } else {
                unset($this->vars[$key]);
            }
        }

        /**
         * Gets a Template section content.
         *
         * @param string $section Section
         *
         * @return mixed Value
         */
        public function getSection($section) {
            return isset($this->sections[$section]) ? $this->sections[$section] : NULL;
        }

        /**
         * Sets a Template section content.
         *
         * @param mixed  $section Section
         * @param string $content Content
         */
        public function setSection($section, $content = NULL) {
            $this->sections[$section] = $content;
        }

        /**
         * Sets active language
         *
         * @param string $language
         */
        public function setLanguage($language) {
            $this->selectedLanguage = $language;
        }

        /**
         * Gets the current language
         *
         * @return mixed|string
         */
        public function getLanguage() {
            return $this->selectedLanguage;
        }

        /**
         * Sets active theme
         *
         * @param string $theme
         */
        public function setTheme($theme) {
            $this->theme = $theme;
        }

        /**
         * Gets the current language
         *
         * @return mixed|string
         */
        public function getTheme() {
            return $this->theme;
        }

        /**
         * Checks if a Template section is set.
         *
         * @param string $section Section
         *
         * @return boolean If section exists
         */
        public function hasSection($section) {
            return isset($this->sections[$section]);
        }

        /**
         * Unsets a Template section. If no section is passed in, clear all sections.
         *
         * @param string $section Section
         */
        public function clearSection($section = NULL) {
            if(is_null($section)) {
                $this->sections = array();
            } else {
                unset($this->sections[$section]);
            }
        }

        /**
         * Sets the layout
         *
         * @param string $layout
         */
        public function setLayout($layout) {
            $this->layout = $layout;
        }

        /**
         * Gets the current layout
         *
         * @return mixed|string
         */
        public function getLayout() {
            return $this->layout;
        }


        /**
         * Set the active section to manipulate the content.
         *
         * @param string $section Section
         */
        public function section($section) {
            $this->selectedSection = $section;
            ob_start();
        }

        /**
         * Set the active section content
         *
         * @throws Exception
         */
        public function endSection() {
            if(!is_null($this->selectedSection)) {
                $this->setSection($this->selectedSection, ob_get_clean());
                $this->selectedSection = NULL;
            } else {
                throw new Exception("Can not end section when there is no selected section!");
            }
        }

        /**
         * Adds parent content to active section.
         */
        public function parent() {
            if(!is_null($this->selectedSection)) {
                echo "<!--@parentViewContentFor" . $this->selectedSection . "-->";
            }
        }

        /**
         * Prints active section.
         *
         * @throws Exception
         */
        public function show() {
            if(!is_null($this->selectedSection)) {
                $parentContent = ob_get_clean();
                $newContent    = strpos($this->getSection($this->selectedSection), "<!--@parentViewContentFor" . $this->selectedSection . "-->") === FALSE ? ($this->hasSection($this->selectedSection) ? $this->getSection($this->selectedSection) : $parentContent) : str_replace("<!--@parentViewContentFor" . $this->selectedSection . "-->", $parentContent, $this->getSection($this->selectedSection));
                echo $newContent;
                $this->selectedSection = NULL;
            } else {
                throw  new Exception("Can not show section when there is no selected section!");
            }
        }


        /**
         * Executes View and returns Response
         *
         * @param string $viewname Viewname
         * @param mixed  $model    Model
         * @param bool   $partial  Is Partial
         *
         * @return Response
         */
        public function getContent($viewname, $model = NULL, $partial = FALSE) {
            $this->body = $this->fetchView($viewname, $model);
            if($partial === TRUE || empty($this->layout)) {
                $this->response->write($this->body);
            } else {
                $this->response->write($this->fetchView($this->layout, $model));
            }

            return $this->response;
        }

        /**
         * Prints a section
         *
         * @param string $section
         * @param bool   $required
         *
         * @throws Exception if Section is Required and Not Exists
         */
        public function renderSection($section, $required = FALSE) {
            if($required && !$this->hasSection($section)) {
                throw new Exception("Template requires section: " . $section);
            }
            if($this->hasSection($section)) {
                echo $this->getSection($section);
            }
        }

        /**
         * Prints Template Body
         */
        public function renderBody() {
            if(!is_null($this->body)) {
                echo $this->body;
            }
        }

        /**
         * @param string $viewname Viewname
         * @param mixed  $model    Model
         */
        public function renderView($viewname, $model = NULL) {
            $content = $this->fetchView($viewname, $model);
            echo $content;
        }


        /**
         * Gets the output of a executed View.
         *
         * @param string $viewname Viewname
         * @param array  $model    Model
         *
         * @return string Output of Template
         */
        private function fetchView($viewname, $model = NULL) {
            ob_start();
            $this->executeView($viewname, $model);
            $output = ob_get_clean();

            return $output;
        }

        /**
         * Renders a Template.
         *
         * @param string $viewname Viewname
         * @param mixed  $model    Model
         *
         * @throws Exception If Template file not found
         */
        private function executeView($viewname, $model = NULL) {
            $this->template = $this->getViewPath($viewname);

            if(!file_exists($this->template)) {
                throw new Exception("Template file not found: {$this->template}.");
            }

            include $this->template;
        }

        /**
         * Checks if a View file exists.
         *
         * @param string $viewname Viewname
         *
         * @return bool View file exists
         */
        public function hasView($viewname) {
            return file_exists($this->getViewPath($viewname));
        }

        /**
         * Gets the full path to a Template file.
         *
         * @param string $viewname Template file
         *
         * @return string Template file location
         */
        private function getViewPath($viewname) {
            if((substr($viewname, -4) != '.php')) {
                $viewname .= '.php';
            }
            if((substr($viewname, 0, 1) == '/')) {
                return './app/Views/' . $this->theme . $viewname;
            } else {
                return './app/Views/' . $this->theme . '/' . $viewname;
            }
        }

        /**
         * Displays escaped output.
         *
         * @param string $str String to escape
         *
         * @return string Escaped string
         */
        public function e($str) {
            echo htmlentities($str, ENT_COMPAT, "UTF-8");
        }

        /**
         * Gets translated key
         *
         * @param       $key
         * @param array $values
         * @param null  $language
         *
         * @return mixed|null
         */
        public function translate($key, $values = array(), $language = NULL) {
            if(empty($key)) {
                return NULL;
            }
            if(is_null($language)) {
                $language = $this->selectedLanguage;
            }

            //If language array is nor defined, we will define it.
            if(!isset($this->languages[$language])) {
                $this->loadTranslation($language);
            }


            //Let's control if the key exists and return
            $returnText = isset($this->languages[$language][$key]) ? $this->languages[$language][$key] : ($language == Wow::get("app/language") ? $key : $this->translate($key, $values, Wow::get("app/language")));
            foreach($values as $k => $v) {
                $returnText = str_replace(":" . $k, $v, $returnText);
            }

            return $returnText;
        }

        /**
         * Load translations from files
         *
         * @param string $language
         *
         * @throws Exception
         */
        private function loadTranslation($language) {
            $langFiles = glob(__DIR__ . '/../../../app/Languages/' . $language . '/*.php');
            $lang      = array();
            try {
                foreach($langFiles as $file) {
                    $lang[basename($file, ".php")] = include $file;
                }
            } catch(Exception $e) {
                throw new Exception("Please control your (" . $language . ") language files. All files must return array!");
            }
            $this->setTranslations($lang, $language);
        }


        /**
         * Set Translation array
         *
         * @param array  $array
         * @param string $language
         * @param string $path
         */
        private function setTranslations(array $array, $language, $path = NULL) {
            foreach($array as $k => $v) {
                if(!is_array($v)) {
                    $this->languages[$language][$path . "/" . $k] = $v;
                } else {
                    $this->setTranslations($v, $language, is_null($path) ? $k : $path . "/" . $k);
                }
            }
        }
    }

