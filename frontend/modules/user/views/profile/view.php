<?php
/* @var $this yii\web\View */
/* @var $user yii\frontend\models\User */

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\helpers\HtmlPurifier;
?>


<h3><?= Html::encode($user->username);?></h3>
<p><?= HtmlPurifier::process($user->about);?></p>
<hr>
<a href="<?= Url::to(['/user/profile/subscribe', 'id' => $user->getId()]);?>" class="btn btn-info">Subscribe</a>
<a href="<?= Url::to(['/user/profile/unsubscribe', 'id' => $user->getId()]);?>" class="btn btn-info">Unsubscribe</a>

<hr>
<h5>Friends, who are also following <?= Html::encode($user->username)?>:</h5>
<div class="row">
    <?php foreach ($currentUser->getMutualSubscriptionsTo($user) as $item):?>
    <div class="col-md-12">
        <a href="<?= Url::to(['/user/profile/view', 'nickname' => ($item['nickname']) ? $item['nickname'] : $item['id']]);?>">
            <?= Html::encode($item['username'])?>
        </a>
    </div>
    <?php endforeach;?>
</div>
<hr>
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#subscriptions">
  Subscriptions <?= $user->countSubscriptions();?>
</button>
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#followers">
  Followers <?= $user->countFollowers();?>
</button>

<!-- Modal Suscriptions -->
<div class="modal fade" id="subscriptions" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">My Subscriptions</h4>
      </div>
      <div class="modal-body">
        <?php foreach($user->getSubscriptions() as $subscription):?>
          <div class="col-md-12">
              <a href="<?= Url::to(['/user/profile/view', 'nickname' => ($subscription['nickname']) ? $subscription['nickname'] : $subscription['id']]);?>">
              <?= HTML::encode($subscription['username']);?>    
              </a>;
          </div>
          <?php endforeach;?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Followers -->
<div class="modal fade" id="followers" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">My followers</h4>
      </div>
      <div class="modal-body">
        <?php foreach($user->getFollowers() as $follower):?>
          <div class="col-md-12">
              <a href="<?= Url::to(['/user/profile/view', 'nickname' => ($follower['nickname']) ? $follower['nickname'] : $follower['id']]);?>">
              <?= HTML::encode($follower['username']);?>    
              </a>;
          </div>
          <?php endforeach;?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>