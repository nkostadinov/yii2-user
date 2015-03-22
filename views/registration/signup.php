<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\forms\SignupForm */

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to signup:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                <?php
                    if(isset($account))
                        echo $form->field($account, 'id')->hiddenInput()->label(false);
                ?>
                <?= $form->field($model, 'username') ?>
                <?= $form->field($model, 'email') ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
            <?php
            $regions = array(
                'Africa' => DateTimeZone::AFRICA,
                'America' => DateTimeZone::AMERICA,
                'Antarctica' => DateTimeZone::ANTARCTICA,
                'Asia' => DateTimeZone::ASIA,
                'Atlantic' => DateTimeZone::ATLANTIC,
                'Europe' => DateTimeZone::EUROPE,
                'Indian' => DateTimeZone::INDIAN,
                'Pacific' => DateTimeZone::PACIFIC
            );

            $timezones = array();
            foreach ($regions as $name => $mask)
            {
                $zones = DateTimeZone::listIdentifiers($mask);
                foreach($zones as $timezone)
                {
                    // Lets sample the time there right now
                    $time = new DateTime(NULL, new DateTimeZone($timezone));

                    // Us dumb Americans can't handle millitary time
                    $ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';

                    // Remove region name and add a sample time
                    $timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
                }
            }


            // View
//            print '<label>Select Your Timezone</label><select id="timezone">';
//            foreach($timezones as $region => $list)
//            {
//                print '<optgroup label="' . $region . '">' . "\n";
//                foreach($list as $timezone => $name)
//                {
//                    print '<option name="' . $timezone . '">' . $name . '</option>' . "\n";
//                }
//                print '<optgroup>' . "\n";
//            }
//            print '</select>';
            ?>
            <?= $form->field($model, 'time_zone')->dropDownList($timezones) ?>

                <div class="form-group">
                    <?= Html::submitButton('Signup', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->registerJs("
    var tz = jstz.determine().name();
    $('#signupform-time_zone').val(tz);
");