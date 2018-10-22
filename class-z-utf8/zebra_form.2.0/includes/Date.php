<?php

/**
 *  Class for date controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2011 Stefan Gabos
 *  @package    Controls
 */
class Zebra_Form_Date extends Zebra_Form_Control
{

    /**
     *  Adds a date control to the form.
     *
     *  <b>Do not instantiate this class directly! Use the {@link Zebra_Form::add() add()} method instead!</b>
     *
     *  The output of this control will be a {@link Zebra_Form_Text textbox} control with an icon to the right of it.<br>
     *  Clicking the icon will open an inline JavaScript date picker.<br>
     *  The date picker was created by {@link http://www.electricprism.com/aeron/ Aeron Glemann} and can be found
     *  {@link http://www.electricprism.com/aeron/calendar/ here}.
     *
     *  <code>
     *  //  create a new form
     *  $form = new Zebra_Form('my_form');
     *
     *  /**
     *   *  add a date control to the form
     *   *  the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *   *  for PHP 5+ there is no need for it
     *   {@*}
     *  $obj = &$form->add('date', 'my_date', date('Y-m-d'));
     *
     *  //  set the date's format
     *  $obj->format('Y-m-d');
     *
     *  // don't forget to always call this method before rendering the form
     *  if ($form->validate()) {
     *      // put code here
     *  }
     *
     *  //  output the form using an automatically generated template
     *  $form->render();
     *  </code>
     *
     *  @param  string  $id             Unique name to identify the control in the form.
     *
     *                                  The control's <b>name</b> attribute will be the same as the <b>id</b> attribute!
     *
     *                                  This is the name to be used when referring to the control's value in the
     *                                  POST/GET superglobals, after the form was submitted.
     *
     *                                  This is also the name of the variable to be used in the template file, containing
     *                                  the generated HTML for the control.
     *
     *                                  <code>
     *                                  /**
     *                                   *  in a template file, in order to print the generated HTML
     *                                   *  for a control named "my_date", one would use:
     *                                   {@*}
     *                                  echo $my_date;
     *                                  </code>
     *
     *  @param  string  $default        (Optional) Default date, formatted according to {@link format() format}.
     *
     *  @param  array   $attributes     (Optional) An array of attributes valid for
     *                                  {@link http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.4 input}
     *                                  controls (size, readonly, style, etc)
     *
     *                                  Must be specified as an associative array, in the form of <i>attribute => value</i>.
     *                                  <code>
     *                                  //  setting the "readonly" attribute
     *                                  $obj = &$form->add(
     *                                      'date',
     *                                      'my_date',
     *                                      '',
     *                                      array(
     *                                          'readonly' => 'readonly'
     *                                      )
     *                                  );
     *                                  </code>
     *
     *                                  See {@link Zebra_Form_Control::set_attributes() set_attributes()} on how to set
     *                                  attributes, other than through the constructor.
     *
     *                                  The following attributes are automatically set when the control is created and
     *                                  should not be altered manually:<br>
     *
     *                                  <b>type</b>, <b>id</b>, <b>name</b>, <b>value</b>, <b>class</b>
     *
     *  @return void
     */
    function Zebra_Form_Date($id, $default = '', $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::Zebra_Form_Control();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // and will not be rendered by the _render_attributes() method
        $this->private_attributes = array(
        
            'locked',
            'disable_xss_filters',
            
        );

        // set the javascript attributes of this control
        // these attributes will be used by the date picker javascript object
        $this->javascript_attributes = array(

            'format',
            'blocked',
            'days',
            'direction',
            'draggable',
            'months',
            'navigation',
            'offset',
            'pad',
            'tweak'

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(
            
                'type'      =>  'text',
                'name'      =>  $id,
                'id'        =>  $id,
                'value'     =>  $default,
                'class'     =>  'control text date',

                'format'            =>  'Y-m-d',
                'blocked'           =>  null,
                'days'              =>  null,
                'direction'         =>  null,
                'draggable'         =>  null,
                'months'            =>  null,
                'navigation'        =>  null,
                'offset'            =>  null,
                'pad'               =>  null,
                'tweak'             =>  null,

            )
            
        );
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
        
    }

    /**
     *  Disables selection of specific dates or range of dates in the calendar
     *
     *  Description taken from the calendar's {@link http://www.electricprism.com/aeron/calendar/#manual manual}:
     *
     *  @param  string  $blocked    The syntax is similar to cron:
     *
     *                              the values are separated by spaces and may contain * (asterisk) - (dash) and ,
     *                              (comma) delimiters.
     *
     *                              -   '1 1 2007' would disable January 1, 2007
     *                              -   '* 1 2007' would disable all days (wildcard) in January, 2007
     *                              -   '1-10 1 2007' would disable January 1 through 10, 2007
     *                              -   '1,10 1 2007' would disable January 1 and 10, 2007
     *
     *                              In combination:
     *
     *                              -   '1-10,20,22,24 1-3 *' would disable 1 through 10, plus the 22nd and 24th of January
     *                                  through March for every (wildcard) year.
     *
     *                              There is an optional additional value which is day of the week (0 - 6 with 0 being
     *                              Sunday).
     *
     *                              -   '0 * 2007 0,6' would disable all weekends (saturdays and sundays) in 2007
     *
     *  @return void
     */
    function blocked($blocked) {

        // set the date picker's attribute
        $this->set_attributes(array('blocked'=>'["' . $blocked . '"]'));

    }

    /**
     *  Sets the calendar's direction (show dates from the future or from the past)
     *
     *  Description taken from the calendar's {@link http://www.electricprism.com/aeron/calendar/#manual manual}:
     *
     *  @param  float   $direction      A positive or negative integer that determines the calendar's direction:
     *
     *                                  -   n (a positive number) the calendar is future-only beginning at n days after
     *                                      today
     *
     *                                  -   n (a negative number) the calendar is past-only ending at n days before today
     *
     *                                  -   0 (zero) the calendar has no future or past restrictions
     *
     *                                  Note if you would like the calendar to be directional starting from today–as
     *                                  opposed to (1) tomorrow or (-1) yesterday–use a positive or negative fraction,
     *                                  such as direction: .5 (future-only, starting today).
     *
     *                                  Default is <b>0</b>.
     *
     *  @return void
     */
    function direction($direction)
    {

        // set the date picker's attribute
        $this->set_attributes(array('direction'=>$direction));

    }

    /**
     *  Specified whether the calendar can be dragged around with the mouse in case the calendar obstructs (or is
     *  obstructed by) some other element on the page.
     *
     *  Description taken from the calendar's {@link http://www.electricprism.com/aeron/calendar/#manual manual}:
     *
     *  @param  boolean     $draggable  If set to TRUE the calendar will be draggable or the calendar will have a fixed
     *                                  position if set to FALSE
     *
     *                                  By default, the calendar is not draggable.
     *
     *  @return void
     */
    function draggable($draggable) {

        // set the date picker's attribute
        $this->set_attributes(array('draggable'=>$draggable));

    }

    /**
     *  Sets the format of the date
     *
     *  Description taken from the calendar's {@link http://www.electricprism.com/aeron/calendar/#manual manual}:
     *
     *  @param  string  $format     The format of the date
     *
     *                              Accepts the following characters for date formatting: d, D, j, l, N, w, S, F, m, M,
     *                              n, Y, y borrowing syntax from ({@link http://www.php.net/manual/en/function.date.php PHP's date function})
     *
     *                              Default format is <b>Y-m-d</b>
     *
     *  @return void
     */
    function format($format) {

        // set the date picker's attribute
        $this->set_attributes(array('format'=>$format));

    }

    /**
     *  Returns the generated HTML code for the control.
     *
     *  <i>This method is automatically called by the {@link Zebra_Form::render() render()} method!</i>
     *
     *  @return string  The generated HTML code for the control
     */
    function toHTML()
    {

        // get some attributes of the control
        $attributes = $this->get_attributes(array('name'));
        
        return '
            <div>
                <input ' . $this->_render_attributes() . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '>
                <div class="clear"></div>
            </div>
        ';

    }

}

?>
