<div class="row">
    <div class="cell"><?php echo $label_name . $name?></div>
    <div class="cell"><?php echo $label_email . $email?></div>
    <div class="clear"></div>
</div>
<div class="row even">
    <?php echo $label_department . $department . $department_other?>
</div>
<div class="row">
    <div class="cell">
        <?php echo $label_room?>
        <div class="cell"><?php echo $room_A?></div>
        <div class="cell"><?php echo $label_room_A?></div>
        <div class="clear"></div>
        <div class="cell"><?php echo $room_B?></div>
        <div class="cell"><?php echo $label_room_B?></div>
        <div class="clear"></div>
        <div class="cell"><?php echo $room_C?></div>
        <div class="cell"><?php echo $label_room_C?></div>
        <div class="clear"></div>
    </div>
    <div class="cell" style="margin-left: 20px">
        <?php echo $label_extra?>
        <div class="cell"><?php echo $extra_flipcharts?></div>
        <div class="cell"><?php echo $label_extra_flipcharts?></div>
        <div class="clear"></div>
        <div class="cell"><?php echo $extra_plasma?></div>
        <div class="cell"><?php echo $label_extra_plasma?></div>
        <div class="clear"></div>
        <div class="cell"><?php echo $extra_beverages?></div>
        <div class="cell"><?php echo $label_extra_beverages?></div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<div class="row even">
    <div class="cell"><?php echo $label_date . $date?></div>
    <div class="cell" style="margin-left: 10px"><?php echo $label_time . $time?></div>
    <div class="clear"></div>
</div>
<div class="row last">
    <?php echo $btnsubmit?>
</div>