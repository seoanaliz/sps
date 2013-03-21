<script src="http://vk.com/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
<script type="text/javascript">
    var controlsRoot = '{web:controls://}';
    var vkAppId = <?= AuthVkontakte::$AppId ?>;
    VK.init({
        apiId: window.vkAppId,
        nameTransportPath: '/xd_receiver.htm'
    });
</script>
<?= JsHelper::Flush(); ?>
</body>
</html>