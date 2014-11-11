<!-- Footer ================================================================== -->
<div  id="footerSection">
    <div class="container">
        <div class="row">
            <div class="span3">
                <h5><?= Yii::t('app', 'Account') ?></h5>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="/login"><?= Yii::t('app', 'Login') ?></a>
                    <a href="/signup"><?= Yii::t('app', 'Signup') ?></a>
                <?php else: ?>
                    <a href="/cabinet"><?= Yii::t('app', 'Personal cabinet') ?></a>
                    <a href="/cabinet/orders"><?= Yii::t('app', 'Orders list') ?></a>
                    <a href="/logout"><?= Yii::t('app', 'Logout') ?></a>
                <?php endif; ?>
            </div>
            <div class="span3">
                <h5><?= Yii::t('app', 'Information') ?></h5>
                <a href="/catalog"><?= Yii::t('app', 'Catalog') ?></a>
                <a href="/contacts"><?= Yii::t('app', 'Contacts') ?></a>
                <a href="/delivery"><?= Yii::t('app', 'Delivery') ?></a>
            </div>
            <div class="span3">
                <h5><?= Yii::t('app', 'Our offers') ?></h5>
                <a href="/special-offer"><?= Yii::t('app', 'Specials Offer') ?></a>
            </div>
            <div id="socialMedia" class="span3 pull-right">
                <h5><?= Yii::t('app', 'Social media') ?></h5>
                <a href="#"><img width="60" height="60" src="/demo/images/facebook.png" title="facebook" alt="facebook"/></a>
                <a href="#"><img width="60" height="60" src="/demo/images/twitter.png" title="twitter" alt="twitter"/></a>
                <a href="#"><img width="60" height="60" src="/demo/images/youtube.png" title="youtube" alt="youtube"/></a>
            </div>
        </div>
        <p class="pull-right">&copy; DotPlant 2014</p>
    </div><!-- Container End -->
</div>

<script src="/demo/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/demo/js/google-code-prettify/prettify.js"></script>

<script src="/demo/js/bootshop.js"></script>
<script src="/demo/js/jquery.lightbox-0.5.js"></script>


<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>