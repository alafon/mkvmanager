<style type="text/css">

a {
    color: black;
}

a:active {
    color: black;
}

div.listingItem {
    width: 100%;
    margin: 16px auto;
    float: left;
    border: 1px solid black;
}

div.showContainer {
    padding: 8px;
    background-color: #eee;
    /*height: 180px;*/
}

div.showInfos {
    float: left;
    width: 100px;
}

div.showInfos img.link {
    margin-top: 8px;
}

div.showDetails {
    padding-left: 8px;
}

div.showDetails h2 {
    font-size: 1em;
    margin-top: 0px;
    margin-bottom: 0px;
}

div.showDetails table td {
    padding: 3px 6px;
}

div.showDetails table td.episodeID {
    width: 45px;
    text-align: center;
}

div.showDetails table td.episodeName {
    width: 350px;
}

</style>

<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.bpopup-0.4.1.min.js"></script>
<script type="text/javascript">
</script>

<? foreach( $this->shows as $showName => $episodeFiles ): ?>
    <a name="<?=anchorLink($showName)?>"></a>
    <div class="listingItem">
    <div class="showContainer">
        <div class="showInfos">
            <img class="poster" src="/tvshow/image/<?=$showName?>:folder.jpg" height="130" />
            <a href=""><img class="link" src="/images/icons/sickbeard_115x55.png" width="88" /></a>
        </div>
        <div class="showDetails">
            <h2><?=$showName;?></h2>
            <table class="listEpisodes">
            <? $displayed = 0; ?>
                <? foreach( $episodeFiles as $episodeFile ): ?>
                <tr>
                    <?/* @var $episodeFile TVEpisodeFile */?>
                    <td class="episodeID"><?=$episodeFile->seasonNumber?>x<?=$episodeFile->episodeNumber?></td>
                    <td class="episodeName">
                    <a class="episode"
                            href="/ajax/searchsubtitles/<?=rawurlencode( $episodeFile->filename )?>/<?//=$episodeFile->downloadedFile != '' ? rawurlencode( $episodeFile->downloadedFile ) : 'none' ?>">
                            <?=$episodeFile->episodeName?></a>
                    </td>
                    <td>
                        Merged subtitles ?
                    </td>
                    <td>
                    <? if ( $episodeFile->hasSubtitleFiles ):?>
                    <? foreach( $episodeFile->subtitleFiles as $qualityID => $subtitles ):?>
                        <div class"qualityListed">
                            <?=$qualityID;?> :
                            <? foreach( $subtitles as $subtitle ):?>
                            <input type="checkbox" />
                            <label><?=basename($subtitle);?></label>
                            <? endforeach;?>
                        </div>
                    <? endforeach;?>
                    <? else: ?>
                    <? endif;?>
                    </td>
                    <td>
                    <? if( $episodeFile->hasSubtitleFiles ):?>
                        <a class="generateCommand" href="/ajax/generate-command/<?=rawurlencode( $episodeFile->filename )?>">mkvmerge</a>
                    <? endif;?>
                    </td>
                    <? if( $displayed > 5 ):?>
                    <? break; endif; $displayed++;?>
                </tr>
                <? endforeach ?>
            </table>
            <ul class="icon listEpisodes">
            </ul>
        </div>
    </div>
    </div>
<? endforeach ?>

<?php
function anchorLink( $showName )
{
    return preg_replace( '/[^a-z0-9]/i', '', $showName );
}
?>
