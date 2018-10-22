<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

    <head>

        <title>Zebra_Form, a PHP class for generating and validating HTML forms</title>

        <meta http-equiv="content-type" content="text/html;charset=UTF-8">
        <meta http-equiv="Content-Script-Type" content="text/javascript">
        <meta http-equiv="Content-Style-Type" content="text/css">

        <link rel="stylesheet" href="public/css/reset.css" type="text/css">
        <link rel="stylesheet" href="libraries/highlight/public/css/ir_black.css" type="text/css">
        <link rel="stylesheet" href="public/css/style.css" type="text/css">
        <link rel="stylesheet" href="../public/css/zebra_form.css" type="text/css">

        <script type="text/javascript" src="public/javascript/mootools-core-1.3.1.js"></script>
        <script type="text/javascript" src="public/javascript/mootools-more-1.3.1.1.js"></script>
        <script type="text/javascript" src="../public/javascript/zebra_form.js"></script>
        <script type="text/javascript" src="libraries/highlight/public/javascript/highlight.js"></script>
        <script type="text/javascript" src="public/javascript/functions.js"></script>

    </head>

    <body>

        <div class="header"><a href="http://stefangabos.ro/">Stefan Gabos | <span>webdeveloper</span></a></div>

        <div class="example">
            Example for: <a href="http://stefangabos.ro/php-libraries/zebra-form/">Zebra_Form a PHP library that simplifies
            the process of creating beautiful, secure, and functional HTML forms</a>
        </div>

        <table>

        	<tr>

        		<td valign="top">

                    <?php

                        $demos = array(
                            'A login form'  =>  array('login', array(
                                'Auto template, vertical'       =>  'vertical',
                                'Auto template, horizontal'     =>  'horizontal',
                                'Auto template, labels inside'  =>  'labels-inside',
                                'Custom template'               =>  'custom',
                            )),
                            'A contact form'  =>  array('contact', array(
                                'Auto template, vertical'       =>  'vertical',
                                'Auto template, horizontal'     =>  'horizontal',
                                'Custom template'               =>  'custom',
                            )),
                            'A registration form'  =>  array('registration', array(
                                'Auto template, vertical'       =>  'vertical',
                                'Auto template, horizontal'     =>  'horizontal',
                                'Custom template'               =>  'custom',
                            )),
                            'A reservation form'  =>  array('reservation', array(
                                'Auto template, vertical'       =>  'vertical',
                                'Auto template, horizontal'     =>  'horizontal',
                                'Custom template'               =>  'custom',
                            )),
                            'Validation rules'  =>  array('validation', array(
                                'Auto template, vertical'       =>  'vertical',
                            )),
                        );

                        $current_template = isset($_GET['template']) ? strtolower($_GET['template']) : '';

                        $current_example = isset($_GET['example']) && is_file('includes/' . $_GET['example'] . '-' . $current_template . '.php') ? strtolower($_GET['example']) : '';

                    ?>

                    <ul class="navigation default">

                        <?php foreach ($demos as $title => $values):?>

                        <li><?php echo $title?>

                            <ul>

                            <?php foreach ($values[1] as $example => $template):?>

                                <li>

                                    <a href="?example=<?php echo $values[0]?>&amp;template=<?php echo $template?>"<?php echo ($current_example == $values[0] && $current_template == $template ? ' class="selected"' : '')?>><?php echo $example?></a>

                                </li>

                            <?php endforeach?>

                            </ul>

                        </li>

                        <?php endforeach?>
                    </ul>

                </td>

                <td valign="top">

                    <?php if ($example != ''):?>

                    <ul class="tabs float clearfix">

                        <li><a href="javascript:void(0)" class="selected">Demo</a></li>

                        <li><a href="javascript:void(0)">PHP source</a></li>

                        <li><a href="javascript:void(0)">Template source</a></li>

                        <li><a href="javascript:void(0)">Container HTML</a></li>

                        <li><a href="javascript:void(0)">Zebra_Form generated HTML</a></li>

                    </ul>

                    <?php for ($i = 0; $i < 5; $i++):?>

                    <div class="tab clearfix"<?php echo $i == 0 ? ' style="display:block"' : ''?>>

                        <?php if ($i == 0):?>

                            <?php require 'includes/' . $current_example . '-' . $current_template . '.php'?>

                        <?php elseif ($i == 1):?>

                        <?php

                            $php_source = file_get_contents('includes/' . $current_example . '-' . $current_template . '.php');

                            $patterns = array(
                                '/^.*?\<\?php/is',
                                '/\'..\/Zebra\_Form\.php\'/',
                                '/\$form\-\>render\(\'includes\/custom\-templates\/.*?\'\)\;/',
                            );

                            $replacements = array(
                                '<?php',
                                '\'path/to/Zebra_Form.php\'',
                                '$form->render(\'path/to/custom-template.php\');',
                            );

                            $php_source = preg_replace($patterns, $replacements, $php_source);

                        ?>

                        <pre><code><?php

                            echo trim(htmlentities($php_source));

                        ?></code></pre>

                        <?php elseif ($i == 2):?>

                            <?php if ($current_template != 'custom'):?>

                                <p>In this example, the output is automatically generated by Zebra_Form's <strong>render</strong> method.</p>

                            <?php else:?>

                            <pre><code><?php

                                echo trim(htmlentities(file_get_contents('includes/custom-templates/' . $current_example . '.php')));

                            ?></code></pre>

                            <?php endif?>

                        <?php elseif ($i == 3):?>

                        <?php

                            $html_container_source = file_get_contents('includes/container-html/container.html');

                        ?>

                        <pre><code><?php

                            echo trim(htmlentities($html_container_source));

                        ?></code></pre>

                        <?php elseif ($i == 4):?>

                        <p>This is the HTML markup generated by <strong>Zebra_Form</strong>. Can be used as reference
                        for creating custom templates.</p>
                        
                        <pre><code><?php

                            $patterns = array(
                                '/action=\".*?\"/is',
                                '<img src=\".*?process\.php\?captcha=1\&amp\;nocache=[0-9]+\" alt=\"\">',
                            );

                            $replacements = array(
                                'action="path/to/action"',
                                '<img src="path/to/process.php?captcha=1&amp;nocache=1301994859" alt="">',
                            );

                            echo trim(htmlentities(preg_replace($patterns, $replacements, file_get_contents('includes/generated-html/' . $current_example . '-' . $current_template . '.html'))))

                        ?></code></pre>

                        <?php endif?>

                        <?php if ($i == 0):?>

                        <ul class="notes default">
                        	<li>
                                try clicking on the submit button without filling the form and then, as you fill the form, to see the
                                JavaScript validation in action;
                            </li>
                        	<li>
                                disable JavaScript to see the server-side validation in action
                            </li>
                        	<li>
                                although in all my examples I use HTML 4.01 Strict, you can switch to XHTML output by using the
                                <strong>doctye</strong> method;
                            </li>
                        	<li>
                                try the example in another browser and see that it works, out of the box. including IE6!
                            </li>
                        </ul>

                        <?php endif?>

                    </div>

                    <?php endfor?>

                    <?php else:?>

                    Use the links to the left to navigate between examples.

                    <?php endif?>

                </td>

        	</tr>

        </table>

        <div class="clear"></div>

    </body>

</html>
