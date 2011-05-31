<style type="text/css">

a {
    color: black;
}

a:active {
    color: black;
}

a.button {
    border: 1px solid #000;
    padding: 3px;
    background-color: #FFF;
    text-decoration: none;
}

div.showContainer {
    margin: 16px auto;
    float: left;
    border: 1px solid black;
    padding: 8px;
    background-color: #eee;
    /*height: 180px;*/
}

div.showDetails h2 {
    font-size: 1em;
    margin-top: 0px;
    margin-bottom: 0px;
}

div.showDetails table {
    width: 100%;
}

div.showDetails table td {
    padding: 3px 6px;
}

div.showDetails table tr {
    height: 50px;
}

div.showDetails table td.episodeID {
    width: 32px;
    text-align: center;
}

div.showDetails table td.episodeName {
    /*width: 240px;*/
}

div.showDetails table td.col-merged-subtitles,
div.showDetails table td.col-available-subtitles {
    width: 180px;
}

div.subtitle-item {
    border: 1px solid #000;
    padding: 3px;
    margin-left: 8px;
    float: left;
    background-color: #FFF;
}

div.subtitle-item a {
    text-decoration: none;
    top: -1px;
    position: relative;
}

div.subtitle-item img {
    margin-left: 4px;
    margin-top: 4px;
    margin-right: 4px;
}

#shows {
    margin: 16px 8px 8px 16px;
    width: 100%;
    float: left;
}

#shows.reduced-width {
    width: 700px;
}

div.show-selector {
    float: left;
    width: 100px;
    margin-bottom: 8px;
    height: 170px;
}

div.show-selector-reduced {
    float: left;
    width: 42px;
    margin-bottom: 8px;
    height: 50px;
}

div.show-selector-reduced img {
    height: 50px;
}

div.show-selector-reduced a {
    display: none;
}

#bottom-list {
    width: 700px;
    margin: 16px 8px;
    display: none;
}

div.show {
}

#context-menu {
    padding: 8px;
    margin: 16px 16px 8px 8px;
    background-color: #eee;
    float: left;
    border: 1px solid #000;
    display: none;
}

#context-menu p {
    padding: 0px;
    margin: 0px;
}

</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $(".show-selector").click(function(event){
        event.preventDefault();
        $("#shows").addClass("reduced-width");
        $("#context-menu").show("slow");
        //$("#shows :not(#"+this.id+") > img").addClass("reduced-width");
        $("#"+this.id).removeClass("show-selector-reduced")
        $("#shows div:not(#"+this.id+")").addClass("show-selector-reduced");
        $("#bottom-list").hide("slow");

        $.get( $("#"+this.id+" a").attr('href'), function success( data ) {
            if ( data.status == 'ok' )
            {
                html = '<div class="showContainer">';
                html += '<div class="showDetails">';
                html += '<table class="listEpisodes">';
                for ( index in data.episodes )
                {
                    item = data.episodes[index];
                    html += '<tr>';
                    html += '<td class="episodeID">'+item.seasonNumber+'x'+item.episodeNumber+'</td>';
                    html += '<td class="episodeName">'+item.episodeName+'</td>';
                    html += '<td class="col-merged-subtitles">';
                    if( item.cache.hasMergedSubtitles )
                    {
                        for ( mergedSubIndex in item.cache.mergedSubtitles )
                        {
                            html += '<div class="subtitle-item">';
                            mergedSub = item.cache.mergedSubtitles[mergedSubIndex];
                            img = '<img src="/images/flags/'+mergedSub+'.gif" />';
                            html += '<a href="#" title="unknown quality">'+'uq*'+'</a>'+img;
                            html += '</div>';
                        }
                    }
                    else
                        html += 'n/a';
                    html += '</td>';
                    html += '<td class="col-available-subtitles">';
                    if ( item.cache.hasSubtitleFiles )
                    {
                        for ( quality in item.cache.subtitleFiles )
                        {
                            for ( subtitleFileIndex in item.cache.subtitleFiles[quality] )
                            {
                                html += '<div class="subtitle-item">';
                                subtitleFile = item.cache.subtitleFiles[quality][subtitleFileIndex];
                                if ( subtitleFile.language != null )
                                    img = '<img src="/images/flags/'+subtitleFile.language+'.gif" />';
                                else
                                    img = '';
                                html += '<a href="#">'+quality+'</a>'+img;
                                html += '</div>';
                            }
                        }
                    }
                    html += '</td>';
                    html += '</tr>';
                }
                html += '</table>';
                html += '</div>';
                html += '</div>';
                $("#bottom-list").html( html );
                $("#bottom-list").show("slow");
            }
            else if ( data.status == 'ko' )
            {
                $("#bottom-list").html( data.message );
            }
        }, "json" );

        return false;
    });


});
</script>

<div id="shows">
<? /* @var $show TVShowFolder */ ?>
<? foreach( $this->shows as $show ): ?>
    <div class="show-selector" id="<?=$show->systemName?>">
        <img class="poster" src="/tvshow/image/<?=$show->name?>:folder.jpg@88x130" height="130" />
        <a href="/ajax/scansubtitles/<?=rawurlencode( $show->name )?>"><?=$show->name?></a>
    </div>
<? endforeach;?>
</div>
<div id="context-menu">
    <h3 style="margin-top:0px">Subtitles merger</h2>
    <p>Episode : Lorem ipsum</p>
    <p>Filename : Lorem ipsum.mkv</p>
    <p>Already merged : 2</p>
    <p>Selected subtitles : 2/4</p>
    <p style="text-align: right; margin: 15px 0px 6px 0px;">
        <a href="#" class="button">Merge</a>
    </p>
</div>
<div id="bottom-list">
</div>

<?php
function anchorLink( $showName )
{
    return preg_replace( '/[^a-z0-9]/i', '', $showName );
}
?>

<? /* foreach( $episodeFiles as $episodeFile ): ?>
<tr>
    <td class="episodeID"><?=$episodeFile->seasonNumber?>x<?=$episodeFile->episodeNumber?></td>
    <td class="episodeName">
        <a class="episode"
                href="/ajax/searchsubtitles/<?=rawurlencode( $episodeFile->filename )?>/<?//=$episodeFile->downloadedFile != '' ? rawurlencode( $episodeFile->downloadedFile ) : 'none' ?>">
                <?=$episodeFile->episodeName?></a>
    </td>
    <td class="col-merged-subtitles">
    </td>
    <td class="col-available-subtitles">
    <? if ( $episodeFile->hasSubtitleFiles ):?>
    <? foreach( $episodeFile->subtitleFiles as $qualityID => $subtitles ):?>
        <div class="available-subtitles">
            <? foreach( $subtitles as $subtitle ):?>
            <div class="subtitle-item">
            <input type="checkbox" />
            <a href="" title="<?=$subtitle['filename'];?>">
                <?=$subtitle['type'];?>&nbsp;|&nbsp;<?=!is_null($subtitle['language'])?$subtitle['language']:'?';?>
            </a>
            </div>
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
    <? if( $displayed > 2 ):?>
    <? break; endif; $displayed++;?>
</tr>
<? endforeach; */ ?>
