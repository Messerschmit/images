<?php
    use yii\helpers\Html;
    use yii\helpers\HtmlPurifier;
?>


<h3><?= Html::encode($user->username);?></h3>
<p><?= HtmlPurifier::process($user->about);?></p>
<hr>
