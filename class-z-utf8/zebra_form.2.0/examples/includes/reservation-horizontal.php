<h2>A meeting room reservation form</h2>

<p>Notice how the PHP code for the form remains basically unchanged, despite the template variations.<br>
The things that change, are the arguments passed to the <strong>render</strong> method, and the <em>width</em> of
some of the elements.</p>

<?php

    // include the Zebra_Form class
    require '../Zebra_Form.php';

    // instantiate a Zebra_Form object
    $form = new Zebra_Form('form');

    // the label for the "name" field
    $form->add('label', 'label_name', 'name', 'Your name:');

    // add the "name" field
    // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
    // for PHP 5+ there is no need for it
    $obj = & $form->add('text', 'name');

    // set rules
    $obj->set_rule(array(

        // error messages will be sent to a variable called "error", usable in custom templates
        'required' => array('error', 'Name is required!')

    ));

    // "email"
    $form->add('label', 'label_email', 'email', 'Your email address:');

    $obj = & $form->add('text', 'email');

    $obj->set_rule(array(
        'required'  =>  array('error', 'Email is required!'),
        'email'     =>  array('error', 'Email address seems to be invalid!'),
    ));

    // "department"
    $form->add('label', 'label_department', 'department', 'Department:');

    $obj = & $form->add('select', 'department', '', array('other' => true));

    $obj->add_options(array(
        'Marketing',
        'Operations',
        'Customer Service',
        'Human Resources',
        'Sales Department',
        'Accounting Department',
        'Legal Department',
    ));

    $obj->set_rule(array(

        'required' => array('error', 'Department is required!')

    ));

    // "room"
    $form->add('label', 'label_room', 'room', 'Which room would you like to reserve:');

    $obj = & $form->add('radios', 'room', array(
        'A' =>  'Room A',
        'B' =>  'Room B',
        'C' =>  'Room C',
    ));

    $obj->set_rule(array(

        'required' => array('error', 'Room selection is required!')

    ));

    // "extra"
    $form->add('label', 'label_extra', 'extra', 'Extra requirements:');

    $obj = & $form->add('checkboxes', 'extra[]', array(
        'flipchards'    =>  'Flipchart and pens',
        'plasma'        =>  'Plasma TV screen',
        'beverages'     =>  'Coffee, tea and mineral water',
    ));

    // "date"
    $form->add('label', 'label_date', 'date', 'Reservation date');

    $obj = & $form->add('date', 'date');

    $obj->set_rule(array(
        'required'      =>  array('error', 'Date is required!'),
        'date'          =>  array('error', 'Date is invalid!'),
    ));

    // date format
    $obj->format('Y-m-d');

    // selectable dates are starting with the current day
    $obj->direction(.1);

    $form->add('note', 'note_date', 'date', 'Date format is YYYY-MM-DD');

    // "time"
    $form->add('label', 'label_time', 'time', 'Reservation time :');

    $form->add('time', 'time', '', array(
        'hours'     =>  array(9, 10, 11, 12, 13, 14, 15, 16, 17),
        'minutes'   =>  array(0, 30),
    ));

    // "submit"
    $form->add('submit', 'btnsubmit', 'Submit');

    // validate the form
    if ($form->validate()) {

        // do stuff here

    }

    // auto generate output, labels to the left of form elements
    $form->render('*horizontal');

?>