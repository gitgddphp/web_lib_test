<?php

/**
 *  A generic class containing common methods, shared by all the controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2011 Stefan Gabos
 *  @package    Generic
 */
class Zebra_Form_Control extends XSS_Clean
{

    /**
     *  Array of HTML attributes of the element
     *
     *  @var array
     *
     *  @access private
     */
    var $attributes;

    /**
     *  Array of HTML attributes that the control's {@link render_attributes()} method should skip
     *
     *  @var array
     *
     *  @access private
     */
    var $private_attributes;

    /**
     *  Array of validation rules set for the control
     *
     *  @var array
     *
     *  @access private
     */
    var $rules;

    /**
     *  Constructor of the class
     *
     *  @return void
     *
     *  @access private
     */
    function Zebra_Form_Control()
    {

        $this->attributes = array(

            'locked' => false,
            'disable_xss_filters' => false,

        );

        $this->private_attributes = array();

        $this->rules = array();

    }

    /**
     *  Disables XSS filtering of the control's submitted value.
     *
     *  By default, all submitted values are filtered for XSS (Cross Site Scripting) injections. The script will
     *  automatically remove possibly malicious content (event handlers, javascript code, etc). While in general this is
     *  the right thing to do, there may be the case where this behaviour is not wanted: for example, for a CMS where
     *  the WYSIWYG editor inserts JavaScript code.
     *
     *  <code>
     *  //  $obj is a reference to a control
     *  $obj->disable_xss_filters();
     *  </code>
     *
     *  @return void
     */
    function disable_xss_filters()
    {

        // set the "disable_xss_filters" private attribute of the control
        $this->set_attributes(array('disable_xss_filters' => true));

    }

    /**
     *  Returns the values of requested attributes.
     *
     *  <code>
     *  //  create a new form
     *  $form = new Zebra_Form('my_form');
     *
     *  //  add a text field to the form
     *  $obj = &$form->add('text', 'my_text');
     *
     *  //  set some attributes for the text field
     *  $obj->set_attributes(array(
     *      'readonly'  => 'readonly',
     *      'style'     => 'font-size:20px',
     *  ));
     *
     *  //  retrieve the attributes
     *  $attributes = $obj->get_attributes(array('readonly', 'style'));
     *
     *  /**
     *   *  the result will be an associative array
     *   *
     *   *  $attributes = Array(
     *   *      [readonly]  => "readonly",
     *   *      [style]     => "font-size:20px"
     *   *  )
     *   *
     *   {@*}
     *  </code>
     *
     *  @param  mixed   $attributes     A single or an array of attributes for which the values to be returned.
     *
     *  @return array                   Returns an associative array where keys are the attributes and the values are
     *                                  each attribute's value, respectively.
     */
    function get_attributes($attributes)
    {

        // initialize the array that will be returned
        $result = array();

        // if the request was for a single attribute
        if (!is_array($attributes)) {

            // treat it as an array of attributes
            $attributes = array($attributes);

        }

        // iterate through the array of attributes to look for
        foreach ($attributes as $attribute) {

            // if attribute exists
            if (array_key_exists($attribute, $this->attributes)) {

                // populate the $result array
                $result[$attribute] = $this->attributes[$attribute];

            }

        }

        // return the results
        return $result;

    }

    /**
     *  Returns the control's value <b>after</b> the form was submitted.
     *
     *  <i>This method is automatically called by the form's {@link Zebra_Form::validate() validate()} method!</i>
     *
     *  <code>
     *  //  $obj is a reference to a control
     *  $obj->get_submitted_value();
     *  </code>
     *
     *  @return void
     */
    function get_submitted_value()
    {

        // get some attributes of the control
        $attribute = $this->get_attributes(array('name', 'type', 'value', 'disable_xss_filters', 'locked'));

        // if control's value is not locked to the default value
        if ($attribute['locked'] !== true) {

            // strip any [] from the control's name (usually used in conjunction with multi-select select boxes and
            // checkboxes)
            $attribute['name'] = preg_replace('/\[\]/', '', $attribute['name']);

            // reference to the form submission method
            global ${'_' . $this->form_properties['method']};

            $method = & ${'_' . $this->form_properties['method']};

            // if form was submitted
            if (

                isset($method[$this->form_properties['identifier']]) &&

                $method[$this->form_properties['identifier']] == $this->form_properties['name']

            ) {

                // if control is a time picker control
                if ($attribute['type'] == 'time') {

                    // combine hour, minutes and seconds into one single string (values separated by :)
                    // hours
                    $combined = (isset($method[$attribute['name'] . '_hours']) ? $method[$attribute['name'] . '_hours'] : '');
                    // minutes
                    $combined .= ($combined != '' && (isset($method[$attribute['name'] . '_minutes']) || isset($method[$attribute['name'] . '_seconds'])) ? ':' : '') . (isset($method[$attribute['name'] . '_minutes']) ? $method[$attribute['name'] . '_minutes'] : '');
                    // seconds
                    $combined .= ($combined != '' && isset($method[$attribute['name'] . '_seconds']) ? ':' : '') . (isset($method[$attribute['name'] . '_seconds']) ? $method[$attribute['name'] . '_seconds'] : '');

                    // create a super global having the name of our time picker control
                    // (remember, we don't have a control with the time picker's control name but three other controls
                    // having the time picker's control name as prefix and _hours, _minutes and _seconds respectively
                    // as suffix)
                    // we need to do this so that the values will also be filtered for XSS injection
                    $method[$attribute['name']] = $combined;

                    // unset the three temporary fields as we want to return to the user the result in a single field
                    // having the name he supplied
                    unset($method[$attribute['name'] . '_hours']);
                    unset($method[$attribute['name'] . '_minutes']);
                    unset($method[$attribute['name'] . '_seconds']);

                }

                // if control was submitted AND
                if (isset($method[$attribute['name']])) {

                    // create the submitted_value property for the control and
                    // assign to it the submitted value of the control
                    $this->submitted_value = $method[$attribute['name']];

                    // if submitted value is an array
                    if (is_array($this->submitted_value)) {

                        // iterate through the submitted values
                        foreach ($this->submitted_value as $key => $value) {

                            // and also, if magic_quotes_gpc is on (meaning that
                            // both single and double quotes are escaped)
                            if (get_magic_quotes_gpc()) {

                                // strip those slashes
                                $this->submitted_value[$key] = stripslashes($value);

                            }

                        }

                    // if submitted value is not an array
                    } else {

                        // and also, if magic_quotes_gpc is on (meaning that both
                        // single and double quotes are escaped)
                        if (get_magic_quotes_gpc()) {

                            // strip those slashes
                            $this->submitted_value = stripslashes($this->submitted_value);

                        }

                    }

                    // since 1.1
                    if (

                        // if XSS filtering is not disabled
                        $attribute['disable_xss_filters'] !== true


                    ) {

                        // if submitted value is an array
                        if (is_array($this->submitted_value)) {

                            // iterate through the submitted values
                            foreach ($this->submitted_value as $key => $value) {

                                // filter the control's value for XSS injection
                                $this->submitted_value[$key] = htmlspecialchars($this->sanitize($value));

                            }

                        } else {

                            // filter the control's value for XSS injection
                            $this->submitted_value = htmlspecialchars($this->sanitize($this->submitted_value));

                        }

                        // set the respective $_POST/$_GET value to the filtered value
                        $method[$attribute['name']] = $this->submitted_value;

                    }

                // if control is a file upload control and a file was indeed uploaded
                } elseif ($attribute['type'] == 'file' && isset($_FILES[$attribute['name']])) {

                    $this->submitted_value = true;

                // if control was not submitted
                } else {

                    // we set this for those controls that are not submitted even
                    // when the form they reside in is (i.e. unchecked checkboxes)
                    // so that we know that they were indeed submitted but they
                    // just don't have a value
                    $this->submitted_value = false;

                }

            }

            // if control was submitted
            if (isset($this->submitted_value)) {

                // the assignment of the submitted value is type dependant
                switch ($attribute['type']) {

                    // if control is a checkbox
                    case 'checkbox':

                        if (

                            (

	                            // if is submitted value is an array
								is_array($this->submitted_value) &&

	                            // and the checkbox's value is in the array
	                            in_array($attribute['value'], $this->submitted_value)

							// OR
							) ||

                            // assume submitted value is not an array and the
                            // checkbox's value is the same as the submitted value
                            $attribute['value'] == $this->submitted_value

                        ) {

                            // set the "checked" attribute of the control
                            $this->set_attributes(array('checked' => 'checked'));

                        // if checkbox was "submitted" as not checked
                        } else {

                            // and if control's default state is checked
                            if (isset($this->attributes['checked'])) {

                                // uncheck it
                                unset($this->attributes['checked']);

                            }

                        }

                        break;

                    // if control is a radio button
                    case 'radio':

                        if (

                            // if the radio button's value is the same as the
                            // submitted value
                            ($attribute['value'] == $this->submitted_value)

                        ) {

                            // set the "checked" attribute of the control
                            $this->set_attributes(array('checked' => 'checked'));

                        }

                        break;

                    // if control is a select box
                    case 'select':

                        // set the "value" private attribute of the control
                        // the attribute will be handled by the
                        // Zebra_Form_Select::_render_attributes() method
                        $this->set_attributes(array('value' => $this->submitted_value));

                        break;

                    // if control is a file upload control, a hidden control, a password field, a text field or a textarea control
                    case 'file':
                    case 'hidden':
                    case 'password':
                    case 'text':
                    case 'textarea':
                    case 'time':

                        // set the "value" standard HTML attribute of the control
                        $this->set_attributes(array('value' => $this->submitted_value));

                        break;

                }

            }

        }

    }

    /**
     *  Locks the control's value. A <i>locked</i> control will preserve its default value after the form is submitted
     *  even if the user altered it.
     *
     *  <code>
     *  //  $obj is a reference to a control
     *  $obj->lock();
     *  </code>
     *
     *  @return void
     */
    function lock() {

        // set the "locked" private attribute of the control
        $this->set_attributes(array('locked' => true));

    }

    /**
     *  Resets the control's submitted value (empties text fields, unchecks radio buttons/checkboxes, etc).
     *
     *  <i>This method also resets the associated POST/GET/FILES superglobals!</i>
     *
     *  <code>
     *  //  $obj is a reference to a control
     *  $obj->reset();
     *  </code>
     *
     *  @return void
     */
    function reset()
    {

        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};

        // get some attributes of the control
        $attributes = $this->get_attributes(array('type', 'name', 'other'));

        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[\]/', '', $attributes['name']);

        // see of what type is the current control
        switch ($attributes['type']) {

            // control is any of the types below
            case 'checkbox':
            case 'radio':

                // unset the "checked" attribute
                unset($this->attributes['checked']);

                // unset the associated superglobal
                unset($method[$attributes['name']]);

                break;

            // control is any of the types below
            case 'date':
            case 'hidden':
            case 'password':
            case 'select':
            case 'text':
            case 'textarea':

                // simply empty the "value" attribute
                $this->attributes['value'] = '';

                // unset the associated superglobal
                unset($method[$attributes['name']]);

                // if control has the "other" attribute set
                if (isset($attributes['other'])) {

                    // clear the associated superglobal's value
                    unset($method[$attributes['name'] . '_other']);

                }

                break;

            // control is a file upload control
            case 'file':

                // unset the related superglobal
                unset($_FILES[$attributes['name']]);

                break;

            // for any other control types
            default:

                // as long as control is not label, note nor captcha
                if (

                    $attributes['type'] != 'label' &&
                    $attributes['type'] != 'note' &&
                    $attributes['type'] != 'captcha'

                ) {

                    // unset the associated superglobal
                    unset($method[$attributes['name']]);

                }

        }

    }

    /**
     *  Sets one or more of the control's attributes.
     *
     *  <code>
     *  //  create a new form
     *  $form = new Zebra_Form('my_form');
     *
     *  //  add a text field to the form
     *  $obj = &$form->add('text', 'my_text');
     *
     *  //  set some attributes for the text field
     *  $obj->set_attributes(array(
     *      'readonly'  => 'readonly',
     *      'style'     => 'font-size:20px',
     *  ));
     *
     *  //  retrieve the attributes
     *  $attributes = $obj->get_attributes(array('readonly', 'style'));
     *
     *  /**
     *   *  the result will be an associative array
     *   *
     *   *  $attributes = Array(
     *   *      [readonly]  => "readonly",
     *   *      [style]     => "font-size:20px"
     *   *  )
     *   *
     *   {@*}
     *  </code>
     *
     *  @param  array       $attributes     An associative array, in the form of <i>attribute => value</i>.
     *
     *  @param  boolean     $overwrite      Setting this argument to FALSE will instruct the script to append the values
     *                                      of the attributes to the already existing ones (if any) rather then overwriting
     *                                      them.
     *
     *                                      Useful, for adding an extra CSS class to the already existing ones.
     *
     *                                      For example, the {@link Zebra_Form_Text text} control has, by default, the
     *                                      <b>class</b> attribute set and already containing some classes needed both
     *                                      for styling and for JavaScript functionality. If there's the need to add one
     *                                      more class to the existing ones, without breaking styles nor functionality,
     *                                      one would use:
     *
     *                                      <code>
     *                                          //  obj is a reference to a control
     *                                          $obj->set_attributes(array('class'=>'my_class'), true);
     *                                      </code>
     *
     *                                      Default is TRUE
     *
     *  @return void
     */
    function set_attributes($attributes, $overwrite = true)
    {

        // check if $attributes is given as an array
        if (is_array($attributes)) {

            // iterate through the given attributes array
            foreach ($attributes as $attribute => $value) {

                // if the value is to be appended to the already existing one
                // and there is a value set for the specified attribute
                // and the values do not represent an array
                if (!$overwrite && isset($this->attributes[$attribute]) && !is_array($this->attributes[$attribute])) {

                    // append the value
                    $this->attributes[$attribute] = $this->attributes[$attribute] . ' ' . $value;

                } else {

                    // add attribute to attributes array
                    $this->attributes[$attribute] = $value;

                }

            }

        }

    }

    /**
     *  Sets a single or an array of validation rules for the control.
     *
     *  <code>
     *      /* $obj is a reference to a control {@*}
     *      $obj->set_rule(array(
     *          'rule #1'    =>  array($arg1, $arg2, ... $argn),
     *          'rule #2'    =>  array($arg1, $arg2, ... $argn),
     *          ...
     *          ...
     *          'rule #n'    =>  array($arg1, $arg2, ... $argn),
     *      ));
     *      /* where 'rule #1', 'rule #2', 'rule #n' are any of the rules listed below
     *      and $arg1, $arg2, $argn are arguments specific to each rule {@*}
     *  </code>
     *
     *  When a validation rule is not passed, a variable becomes available in the template file, having the name
     *  as specified by the rule's <b>error_block</b> argument and having the value as specified by the rule's
     *  <b>error_message</b> argument.
     *
     *  I usually have at the top of my templates something like (assuming all errors are sent to an error block named
     *  "error"):
     *
     *  <code>
     *  echo (isset($error) ? $error : '');
     *  </code>
     *
     *  One or all error messages can be displayed in an error block.
     *  See the {@link Zebra_Form::$show_all_error_messages show_all_error_messages} property.
     *
     *  <b>Everything related to error blocks applies only for server-side validation.</b><br>
     *  <b>See the {@link Zebra_Form::client_side_validation() client_side_validation()} method for configuring how errors
     *  are to be displayed to the user upon client-side validation.</b>
     *
     *  Available rules are
     *  -   alphabet
     *  -   alphanumeric
     *  -   captcha
     *  -   compare
     *  -   convert
     *  -   custom
     *  -   date
     *  -   datecompare
     *  -   digits
     *  -   email
     *  -   emails
     *  -   filesize
     *  -   float
     *  -   image
     *  -   length
     *  -   number
     *  -   regexp
     *  -   required
     *  -   resize
     *  -   upload
     *
     *  Rules description:
     *
     *  -   <b>alphabet</b>
     *
     *  <code>'alphabet' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides the alphabet (provide
     *      an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only characters from the alphabet (case-insensitive a to z) <b>plus</b> characters
     *  given as additional characters (if any).
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'alphabet' => array(
     *          '-'                                     /* allow alphabet plus dash {@*}
     *          'error',                                /* variable to add the error message to {@*}
     *          'Only alphabetic characters allowed!'   /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>alphanumeric</b>
     *
     *  <code>'alphanumeric' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides the alphabet and
     *      digits 0 to 9 (provide an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only characters from the alphabet (case-insensitive a to z) and digits (0 to 9)
     *  <b>plus</b> characters given as additional characters (if any).
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'alphanumeric' => array(
     *          '-'                                     /* allow alphabet, digits and dash {@*}
     *          'error',                                /* variable to add the error message to {@*}
     *          'Only alphanumeric characters allowed!' /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>captcha</b>
     *
     *  <code>'captcha' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value matches the characters seen in the {@link Zebra_Form_Captcha captcha} image
     *  (therefore, there must be a {@link Zebra_Form_Captcha captcha} image on the form)
     *
     *  Available only for the {@link Zebra_Form_Text text} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'captcha' => array(
     *          'error',                            /* variable to add the error message to {@*}
     *          'Characters not entered correctly!' /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>compare</b>
     *
     *  <code>'compare' => array($control, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>control</i> is the name of a control on the form to compare values with
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is the same as the value of the control indicated by <i>control</i>.
     *
     *  Useful for password confirmation.
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'compare' => array(
     *          'password'                          /* name of the control to compare values with {@*}
     *          'error',                            /* variable to add the error message to {@*}
     *          'Password not confirmed correctly!' /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>convert</b>
     *
     *  <code>'convert' => array($type, $jpeg_quality, $preserve_original_file, $overwrite, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>type</i> the type to convert the image to; can be (case-insensitive) JPG, PNG or GIF
     *
     *  -   <i>jpeg_quality</i>: Indicates the quality of the output image (better quality means bigger file size).
     *
     *      Range is 0 - 100
     *
     *      Available only if <b>type</b> is "jpg".
     *
     *  -   <i>preserve_original_file</i>: Should the original file be preserved after the conversion is done?
     *
     *  -   <i>$overwrite</i>: If a file with the same name as the converted file already exists, should it be
     *      overwritten or should the name be automatically computed.
     *
     *      If a file with the same name as the converted file already exists and this argument is FALSE, a suffix of
     *      "_n" (where n is an integer) will be appended to the file name.
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  This rule will convert an image file uploaded using the <b>upload</b> rule from whatever its type (as long as is one
     *  of the supported types) to the type indicated by <i>type</i>.
     *
     *  Validates if the uploaded file is an image file and <i>type</i> is valid.
     *
     *  This is not actually a "rule", but because it can generate an error message it is included here
     *
     *  You should use this rule in conjunction with the <b>upload</b> and <b>image</b> rules.
     *
     *  If you are also using the <b>resize</b> rule, make sure you are using it AFTER the <b>convert</b> rule!
     *
     *  Available only for the {@link Zebra_Form_File file} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  /* $obj is a reference to a file upload control {@*}
     *  $obj->set_rule(
     *       'convert' => array(
     *          'jpg',                          /* type to convert to {@*}
     *          85,                             /* converted file quality {@*}
     *          false,                          /* preserve original file? {@*}
     *          false,                          /* overwrite if converted file already exists? {@*}
     *          'error',                        /* variable to add the error message to {@*}
     *          'File could not be uploaded!'   /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>custom</b>
     *
     *  Using this rule, custom rules can be applied to the submitted values.
     *
     *  <code>'custom'=>array($callback_function_name, [optional arguments to be passed to the function], $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>callback_function_name</i> is the name of the callback function
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  <i>The callback function's first argument must ALWAYS be the control's submitted value. The optional arguments to
     *  be passed to the callback function will start as of the second argument!</i>
     *
     *  <i>The callback function MUST return TRUE on success or FALSE on failure!</i>
     *
     *  Multiple custom rules can also be set through an array of callback functions:
     *
     *  <code>
     *  'custom' => array(
     *
     *      array($callback_function_name1, [optional arguments to be passed to the function], $error_block, $error_message),
     *      array($callback_function_name1, [optional arguments to be passed to the function], $error_block, $error_message)
     *
     *  )
     *  </code>
     *
     *  <b>If {@link Zebra_Form::client_side_validation() client-side validation} is enabled (enabled by default), the
     *  custom function needs to also be available in JavaScript, with the exact same name as the function in PHP!</b>
     *
     *  For example, here's a custom rule for checking that an entered value is an integer, greater than 21:
     *
     *  <code>
     *  // the custom function in JavaScript
     *  <script type="text/javascript">
     *      function is_valid_number(value)
     *      {
     *          // return false if the value is less than 21
     *          if (value < 21) return false;
     *          // return true otherwise
     *          return true;
     *      }
     *  <&92;script>
     *
     *  // the callback function in PHP
     *  function is_valid_number($value)
     *  {
     *      // return false if the value is less than 21
     *      if ($value < 21) return false;
     *      // return true otherwise
     *      return true;
     *  }
     *
     *  //  create a new form
     *  $form = new Zebra_Form('my_form');
     *
     *  /**
     *   *  add a text control to the form
     *   *  the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *   *  for PHP 5+ there is no need for it
     *   {@*}
     *  $obj = &$form->add('text', 'my_text');
     *
     *  /**
     *   *  set two rules:
     *   *  on that requires the value to be an integer
     *   *  and a custom rule that requires the value to be greater than 21
     *  {@*}
     *  $obj->set_rule(
     *      'number'    =>  array('', 'error', 'Value must be an integer!'),
     *      'custom'    =>  array(
     *          'is_valid_number',
     *          'error',
     *          'Value must be greater than 21!'
     *      )
     *  );
     *  </code>
     *
     *  -   <b>date</b>
     *
     *  <code>'date' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *

     *  Validates if the value is a propper date, formated according to the format set through the
     *  {@link Zebra_Form_Date::format() format()} method.
     *
     *  Available only for the {@link Zebra_Form_Date date} control.
     *
     *  <i>Note that the validation is language dependant: if the form's language is other than English and month names
     *  are expected, the script will expect the month names to be given in that particular language, as set in the
     *  language file!</i>
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'date' => array(
     *          'error',        /* variable to add the error message to {@*}
     *          'Invalid date!' /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>datecompare</b>
     *
     *  <code>'datecompare' => array($control, $comparison_operator, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>control</i> is the name of a date control on the form to compare values with
     *
     *  -   <i>comparison_operator</i> indicates how the value should be, compared to the value of <i>control</i>.<br>
     *      Possible values are <, <=, >, >=
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value satisfies the comparison operator when compared to the other date control's value.
     *
     *  Available only for the {@link Zebra_Form_Date date} control.
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'datecompare' => array(
     *          'another_date'                      /* name of another date control on the form {@*}
     *          '>',                                /* comparison operator {@*}
     *          'error',                            /* variable to add the error message to {@*}
     *          'Date must be after another_date!'  /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>digits</b>
     *
     *  <code>'digits' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides digits (provide
     *      an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only digits (0 to 9) <b>plus</b> characters given as additional characters (if any).
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'digits' => array(
     *          '-'                         /* allow digits and dash {@*}
     *          'error',                    /* variable to add the error message to {@*}
     *          'Only digits are allowed!'  /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>email</b>
     *
     *  <code>'email' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is a properly formatted email address.
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'email' => array(
     *          'error',                    /* variable to add the error message to {@*}
     *          'Invalid email address!'    /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>emails</b>
     *
     *  <code>'emails' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is a properly formatted email address <b>or</b> a comma separated list of properly
     *  formatted email addresses.
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'emails' => array(
     *          'error',                        /* variable to add the error message to {@*}
     *          'Invalid email address(es)!'    /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>filesize</b>
     *
     *  <code>'filesize' => array($file_size, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>file_size</i> is the allowed file size, in bytes
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the size (in bytes) of the uploaded file is not larger than the value (in bytes) specified by
     *  <i>file_size</i>.
     *
     *  <b>Note that $file_size should be lesser or equal to the value of upload_max_filesize set in php.ini!</b>
     *
     *  Available only for the {@link Zebra_Form_File file} control.
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'filesize' => array(
     *          '102400',                           /* maximum allowed file size (in bytes) {@*}
     *          'error',                            /* variable to add the error message to {@*}
     *          'File size must not exceed 100Kb!'  /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>float</b>
     *
     *  <code>'float' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides digits, one dot and one
     *      minus sign (provide an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only digits (0 to 9) and/or <b>one</b> dot (but not as the very first character)
     *  and/or <b>one</b> minus sign (but only if it is the very first character) <b>plus</b> characters given as
     *  additional characters (if any).
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'float' => array(
     *          ''                  /* don't allow any extra characters {@*}
     *          'error',            /* variable to add the error message to {@*}
     *          'Invalid number!'   /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>image</b>
     *
     *  <code>'image' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates only if the uploaded file is a valid GIF, PNG or JPEG image file.
     *
     *  Available only for the {@link Zebra_Form_File file} control.
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'image' => array(
     *          'error',                                /* variable to add the error message to {@*}
     *          'Not a valid GIF, PNG or JPEG file!'    /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>length</b>
     *
     *  <code>'length' => array($minimum_length, $maximum_length, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>minimum_length</i> is the minimum number of characters the values should contain
     *
     *  -   <i>maximum_length</i> is the maximum number of characters the values should contain
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates only if the number of characters of the value is between $minimum_length and $maximum_length.
     *
     *  If an exact length is needed, set both $minimum_length and $maximum_length to the same value.
     *
     *  Set $maximum_length to 0 (zero) if no upper limit needs to be set for the value's length.
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'length' => array(
     *          3,                                              /* minimum length {@*}
     *          6,                                              /* maximum length {@*}
     *          'error',                                        /* variable to add the error message to {@*}
     *          'Value must have between 3 and 6 characters!'   /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>number</b>
     *
     *  <code>'number' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides digits and one
     *      minus sign (provide an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only digits (0 to 9) and/or <b>one</b> minus sign (but only if it is the very
     *  first character) <b>plus</b> characters given as additional characters (if any).
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'number' => array(
     *          ''                  /* don't allow any extra characters {@*}
     *          'error',            /* variable to add the error message to {@*}
     *          'Invalid integer!'  /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>regexp</b>
     *
     *  <code>'regexp' => array($regular_expression, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>regular_expression</i> is the regular expression pattern (without delimiters) to be tested on the value
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value satisfies the given regular expression
     *
     *  Available for the following controls: {@link Zebra_Form_Password password}, {@link Zebra_Form_Text text},
     *  {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'regexp' => array(
     *          '^0123'                         /* the regular expression {@*}
     *          'error',                        /* variable to add the error message to {@*}
     *          'Value must begin with "0123"'  /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>required</b>
     *
     *  <code>'required' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates only if a value exists.
     *
     *  Available for the following controls: {@link Zebra_Form_Checkbox checkbox}, {@link Zebra_Form_Date date},
     *  {@link Zebra_Form_File file}, {@link Zebra_Form_Password password}, {@link Zebra_Form_Radio radio},
     *  {@link Zebra_Form_Select select}, {@link Zebra_Form_Text text}, {@link Zebra_Form_Textarea textarea}
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'required' => array(
     *          'error',            /* variable to add the error message to {@*}
     *          'Field is required' /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>resize</b>
     *
     *  <code>'resize' => array(
     *      $prefix,
     *      $width,
     *      $height,
     *      $preserve_aspect_ratio,
     *      $method,
     *      $background_color,
     *      $enlarge_smaller_images,
     *      $jpeg_quality,
     *      $error_block,
     *      $error_message,
     *  )
     *  </code>
     *
     *  where
     *
     *  -   <i>prefix</i>: If the resized image is to be saved as a new file and the originally uploaded file needs to be
     *      preserved, specify a prefix to be used for the new file. This way, the resized image will have the same name as
     *      the original file but prefixed with the given value (i.e. "thumb_").
     *
     *      Specifying an empty string as argument will instruct the script to apply the resizing to the uploaded image
     *      and therefore overwriting the originally uploaded file.
     *
     *  -   <i>width</i> is the width to resize the image to.
     *
     *      If set to <b>0</b>, the width will be automatically adjusted, depending on the value of the <b>height</b>
     *      argument so that the image preserves its aspect ratio.
     *
     *      If <b>preserve_aspect_ratio</b> is set to TRUE and both this and the <b>height</b> arguments are values
     *      greater than <b>0</b>, the image will be resized to the exact required width and height and the aspect ratio
     *      will be preserved (see the description for the <b>method</b> argument below on how can this be done).
     *
     *      If <b>preserve_aspect_ratio</b> is set to FALSE, the image will be resized to the required width and the
     *      aspect ratio will be ignored.
     *
     *      If both <b>width</b> and <b>height</b> are set to <b>0</b>, a copy of the source image will be created
     *      (<b>jpeg_quality</b> will still apply).
     *
     *      If either <b>width</b> or <b>height</b> are set to <b>0</b>, the script will consider the value of the
     *      <b>preserve_aspect_ratio</b> to bet set to TRUE regardless of its actual value!
     *
     *  -   <i>height</i> is the height to resize the image to.
     *
     *      If set to <b>0</b>, the height will be automatically adjusted, depending on the value of the <b>width</b>
     *      argument so that the image preserves its aspect ratio.
     *
     *      If <b>preserve_aspect_ratio</b> is set to TRUE and both this and the <b>width</b> arguments are values greater
     *      than <b>0</b>, the image will be resized to the exact required width and height and the aspect ratio will be
     *      preserved (see the description for the <b>method</b> argument below on how can this be done).
     *
     *      If <b>preserve_aspect_ratio</b> is set to FALSE, the image will be resized to the required height and the
     *      aspect ratio will be ignored.
     *
     *      If both <b>height</b> and <b>width</b> are set to <b>0</b>, a copy of the source image will be created
     *      (<b>jpeg_quality</b> will still apply).
     *
     *      If either <b>height</b> or <b>width</b> are set to <b>0</b>, the script will consider the value of the
     *      <b>preserve_aspect_ratio</b> to bet set to TRUE regardless of its actual value!
     *
     *  -   <i>preserve_aspect_ratio</i>: If set to TRUE, the image will be resized to the given width and height and the
     *      aspect ratio will be preserved.
     *
     *      Set this to FALSE if you want the image forcefully resized to the exact dimensions given by width and height
     *      ignoring the aspect ratio
     *
     *  -   <i>method</i>: is the method to use when resizing images to exact width and height while preserving aspect
     *      ratio.
     *
     *      If the <b>preserve_aspect_ratio</b> property is set to TRUE and both the <b>width</b> and <b>height</b>
     *      arguments are values greater than <b>0</b>, the image will be resized to the exact given width and height
     *      and the aspect ratio will be preserved by using on of the following methods:
     *
     *      <b>ZEBRA_IMAGE_BOXED</b>: the image will be scalled so that it will fit in a box with the given width and
     *      height (both width/height will be smaller or equal to the required width/height) and then it will be centered
     *      both horizontally and vertically. The blank area will be filled with the color specified by the
     *      <b>background_color</b> argument. (the blank area will be filled only if the image is not transparent!)
     *
     *      <b>ZEBRA_IMAGE_NOT_BOXED</b>: the image will be scalled so that it <i>could</i> fit in a box with the given
     *      width and height but will not be enclosed in a box with given width and height. The new width/height will be
     *      both smaller or equal to the required width/height
     *
     *      <b>ZEBRA_IMAGE_CROP_TOPLEFT</b>: after the image has been scaled so that one if its sides meets the required
     *      width/height and the other side is not smaller than the required height/width, a region of required width and
     *      height will be cropped from the top left corner of the resulted image.
     *
     *      <b>ZEBRA_IMAGE_CROP_CENTER</b>: after the image has been scaled so that one if its sides meets the required
     *      width/height and the other side is not smaller than the required height/width, a region of required width and
     *      height will be cropped from the center of the resulted image.
     *
     *  -   <i>background_color</i> is the hexadecimal color of the blank area (without the #). See the <b>method</b>
     *      argument.
     *
     *  -   <i>enlarge_smaller_images</i>: if set to FALSE, images having both width and height smaller than the required
     *      width and height, will be left untouched (<b>jpeg_quality</b> will still apply).
     *
     *  -   <i>jpeg_quality</i> indicates the quality of the output image (better quality means bigger file size).
     *
     *      Range is 0 - 100
     *
     *      Available only for JPEG files.
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  <b>This rule requires the prior inclusion of the Zebra_Image class!</b>
     *
     *  This is not an actual "rule", but because it can generate an error message it is included here
     *
     *  Available only for the {@link Zebra_Form_File file} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'resize' => array(
     *          'thumb_',                           /* prefix {@*}
     *          '150',                              /* width {@*}
     *          '150',                              /* height {@*}
     *          true,                               /* preserve aspect ratio {@*}
     *          ZEBRA_IMAGE_BOXED,                  /* method to be used {@*}
     *          'FFFFFF',                           /* background color {@*}
     *          true,                               /* enlarge smaller images {@*}
     *          85,                                 /* jpeg quality {@*}
     *          'error',                            /* variable to add the error message to {@*}
     *          'Thumbnail could not be created!'   /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  -   <b>upload</b>
     *
     *  <code>'upload' => array($upload_path, $preserve_original_name, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>upload_path</i> the path where to upload the files to
     *
     *  -   <i>preserve_original_name</i>: should the original file name be kept when uploading the file or should a
     *      random name be generated instead?
     *
     *      Note that when you choose to preserve the name of the uploaded files a suffix of "_n" (where n is an integer)
     *      will be appended to the file name if the file name already exists.
     *
     *      Set this to FALSE (or, for better code readability, you should use the "ZEBRA_FORM_UPLOAD_RANDOM_NAMES"
     *      constant instead of "FALSE") if you choose to have a random name generated automatically.
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the file was successfully uploaded to the folder specified by <b>upload_path</b>.
     *
     *  This is not actually a "rule", but because it can generate an error message it is included here
     *
     *  You should use this rule in conjunction with the <b>filesize</b> rule
     *
     *  Available only for the {@link Zebra_Form_File file} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  /* $obj is a reference to a control {@*}
     *  $obj->set_rule(
     *       'upload' => array(
     *          'tmp',                          /* path to upload file to {@*}
     *          ZEBRA_FORM_UPLOAD_RANDOM_NAMES, /* upload file with random-generated name {@*}
     *          'error',                        /* variable to add the error message to {@*}
     *          'File could not be uploaded!'   /* error message if value doesn't validate {@*}
     *       )
     *  );
     *  </code>
     *
     *  @param  array   $rules  An associative array
     *
     *                          See above how it needs to be specified for each rule
     *
     *  @return void
     */
    function set_rule($rules)
    {

        // continue only if argument is an array
        if (is_array($rules)) {

            // iterate through the given rules
            foreach ($rules as $rule_name => $rule_properties) {

                // make sure the rule's name is lowercase
                $rule_name = strtolower($rule_name);

                // if custom rule
                if ($rule_name == 'custom') {

                    // if more custom rules are specified at once
                    if (is_array($rule_properties[0])) {

                        // iterate through the custom rules
                        foreach ($rule_properties as $rule) {

                            // and add them one by one
                            $this->rules[$rule_name][] = $rule;

                        }

                    // if a single custom rule is specified
                    } else {

                        // save the custom rule to the "custom" rules array
                        $this->rules[$rule_name][] = $rule_properties;

                    }

                // for all the other rules
                } else {

                    // add the rule to the rules array
                    $this->rules[$rule_name] = $rule_properties;

                }

                // for some rules we do some additional settings
                switch ($rule_name) {

                    // we set a reserved attribute for the control by which we're telling the
                    // _render_attributes() method to append a special class to the control when rendering it
                    // so that we can also control user input from javascript
                    case 'alphabet':
                    case 'digits':
                    case 'alphanumeric':
                    case 'number':
                    case 'float':

                        $this->set_attributes(array('onkeypress' => 'javascript:return ' . preg_replace('/\-/', '_', $this->form_properties['name']) . '_object.filter_input(\'' . $rule_name . '\', event' . ($rule_properties[0] != '' ? ', \'' . addcslashes($rule_properties[0], '\'') . '\'' : '') . ');'));

                        break;

                    // if the rule is about the length of the input
                    case 'length':

                        // if there is a maximum of allowed characters
                        if ($rule_properties[1] > 0) {

                            // set the maxlength attribute of the control
                            $this->set_attributes(array('maxlength' => $rule_properties[1]));

                        }

                        break;

                }

            }

        }

    }

    /**
     *  Converts the array with control's attributes to valid HTML markup interpreted by the {@link toHTML()} method
     *
     *  Note that this method skips {@link $private_attributes}
     *
     *  @return string  Returns a string with the control's attributes
     *
     *  @access private
     */
    function _render_attributes()
    {

        // the string to be returned
        $attributes = '';

        // if
        if (

            // control has the "disabled" attribute set
            isset($this->attributes['disabled']) &&

            $this->attributes['disabled'] == 'disabled' &&

            // control is not a radio button
            $this->attributes['type'] != 'radio' &&

            // control is not a checkbox
            $this->attributes['type'] != 'checkbox'

        ) {

            // add another class to the control
            $this->set_attributes(array('class' => 'disabled'), true);

        }

        // iterates through the control's attributes
        foreach ($this->attributes as $attribute => $value) {

            if (

                // if control has no private attributes or the attribute is not  a private attribute
                (!isset($this->private_attributes) || !in_array($attribute, $this->private_attributes)) &&

                // and control has no private javascript attributes or the attribute is not in a javascript private attribute
                (!isset($this->javascript_attributes) || !in_array($attribute, $this->javascript_attributes))

            ) {

                // add attribute => value pair to the return string
                $attributes .=

                    ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $value) . '"';

            }

        }

        // returns string
        return $attributes;

    }

}

?>
