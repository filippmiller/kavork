<script>
  function leave() {
    if ($('.modal:visible').length == 0)
      window.location = "/";
  }

  setInterval("leave()", <?=Yii::$app->cafe->get()->selfservice_timaout;?>000);
</script>