<? if (!empty( $__params[SiteParamHelper::GoogleAnalytics] ) ) { ?>
    <!-- GoogleAnalytics counter -->
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?= $__params[SiteParamHelper::GoogleAnalytics]->value ?>']);
        _gaq.push(['_trackPageview']);
        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
    <!-- /GoogleAnalytics counter -->
<? } ?>

<? if (!empty( $__params[SiteParamHelper::YandexMetrika] ) ) { ?>
    <?
    $__yandexMetrika = $__params[SiteParamHelper::YandexMetrika]->value;
    ?>
    <!-- Yandex.Metrika counter -->
    <div style="display:none;"><script type="text/javascript">
            (function(w, c) {
                (w[c] = w[c] || []).push(function() {
                    try {
                        w.yaCounter{$__yandexMetrika} = new Ya.Metrika({id:{$__yandexMetrika}, enableAll: true});
                    }
                    catch(e) { }
                });
            })(window, 'yandex_metrika_callbacks');
        </script></div>
    <script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
    <noscript><div><img src="//mc.yandex.ru/watch/{$__yandexMetrika}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
<? } ?>

<!--LiveInternet counter-->
<script type="text/javascript"><!--
    document.write("<a style='position:absolute;left:-9999px;top:-9999px' href='http://www.liveinternet.ru/click' "+
        "target=_blank><img src='//counter.yadro.ru/hit?t57.6;r"+
        escape(document.referrer)+((typeof(screen)=="undefined")?"":
        ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
            screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
        ";"+Math.random()+
        "' alt='' title='LiveInternet' "+
        "border='0' width='88' height='31'><\/a>")
    //--></script>
<!--/LiveInternet-->