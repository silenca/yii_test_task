<?php /** @var $type string */ ?>
<?php /** @var $visitDate string */ ?>
<?php /** @var $doctorsSchedule array */ ?>
<?php /** @var $cabinetsList array */ ?>
<div class="vp-date"><?php echo date("d.m.Y", strtotime($visitDate));?></div>
<div class="vp-calendar">
    <?php if(!empty($data)):?>
        <?php foreach ($data as $value):?>
			<div class="vp-column" data-date="<?php echo date("d.m.Y", strtotime($visitDate));?>"
			     data-field="<?php echo $type;?>" data-value="<?php echo $value['name'];?>" data-id="<?php echo $value['oid'];?>">
				<div class="vp-column-head">
                    <?php echo $value['name'];?>
				</div>
                <?php if(!empty($value['gr_time'])):?>
                    <?php foreach ($value['gr_time'] as $time): ?>
						<time class="vp-time <?php echo $time['class'];?>" data-time="<?php echo $time['time'];?>"><?php echo $time['time'];?></time>
                    <?php endforeach;?>
                <?php endif;?>
			</div>
        <?php endforeach;?>
    <?php endif;?>
</div>
