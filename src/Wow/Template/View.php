<?php

    namespace Wow\Template;

    use Wow;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;
    use Exception;

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
        public function __construct(Request $request, Route $route = null) {
            $this->response = new Response();
            $this->html     = new Html($request, $this->response);
            $this->route    = $route;
            $this->setLayout(Wow::get('app.layout'));
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
         * Renders a Template.
         *
         * @param string $file Template file
         * @param array  $data Template data
         * @param string $key  section
         */
        public function render($file, $data = NULL, $key = NULL) {
            if($key !== NULL) {
                $this->setSection($key, $this->fetch($file, $data));
            } else {
                $this->renderTemplate($file, $data);
            }
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
                $newContent    = strpos($this->getSection($this->selectedSection), "<!--@parentViewContentFor" . $this->selectedSection . "-->") === FALSE ? $parentContent : str_replace("<!--@parentViewContentFor" . $this->selectedSection . "-->", $parentContent, $this->getSection($this->selectedSection));
                echo $newContent;
                $this->selectedSection = NULL;
            } else {
                throw  new Exception("Can not show section when there is no selected section!");
            }
        }


        /**
         * Inits Template Body
         *
         * @param string $file
         * @param string $data
         * @param bool   $partial
         */
        public function getContent($file, $data = NULL, $partial = FALSE) {
            $this->body = $this->fetch($file, $data);
            if($partial === TRUE) {
                $this->response->write($this->body);
            } else {
                $this->response->write($this->fetch($this->layout, $data));
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
         * Renders a Template.
         *
         * @param string $file Template file
         * @param array  $data Template data
         *
         * @throws Exception If Template file not found
         */
        public function renderTemplate($file, $data = NULL) {
            $this->template = $this->getTemplate($file);

            if(!file_exists($this->template)) {
                throw new Exception("Template file not found: {$this->template}.");
            }

            if(is_array($data)) {
                $this->vars = array_merge($this->vars, $data);
            }

            extract($this->vars);

            include $this->template;
        }

        /**
         * Gets the output of a Template.
         *
         * @param string $file Template file
         * @param array  $data Template data
         *
         * @return string Output of Template
         */
        public function fetch($file, $data = NULL) {
            ob_start();
            $this->renderTemplate($file, $data);
            $output = ob_get_clean();

            return $output;
        }

        /**
         * Checks if a Template file exists.
         *
         * @param string $file Template file
         *
         * @return bool Template file exists
         */
        public function exists($file) {
            return file_exists($this->getTemplate($file));
        }

        /**
         * Gets the full path to a Template file.
         *
         * @param string $file Template file
         *
         * @return string Template file location
         */
        public function getTemplate($file) {
            if((substr($file, -4) != '.php')) {
                $file .= '.php';
            }
            if((substr($file, 0, 1) == '/')) {
                return $file;
            } else {
                return './app/Views/' . $file;
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
            echo htmlentities($str);
        }
    }

