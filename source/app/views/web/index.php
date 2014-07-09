<div id="event_info">
    <?php echo (!is_null($event)) ? $event['title'] : 'No event'; ?>
</div>
<div id="nowplaying">
    Now Playing
</div>
<div id="requests" class="requests_visible">
    <table cellpadding="0" cellspacing="0" class="list">
    <?php echo $this->listRequests($requests);?>
    </table>
</div>
<div id="request_form"class="glass down">
    <h1 class="toggle">Request Song</h1>
    <div class="glass_content">
        <h2>Fill in the form to request your song</h2>
        <form action="/api/request/submit" method="post" id="request">
            <input type="text" name="artist" class="text auto_comp" id="artist_list" placeholder="Artist" />
            <input type="text" name="title" class="text auto_comp" id="title_list" placeholder="Title" />
            <input type="text" name="dedicate" class="text" id="dedicate" placeholder="Dedicate to" />
            <textarea name="message" class="text" id="message" placeholder="Any comments" rows="4"></textarea>
            <div class="clear"></div>
            <input type="submit" name="send" class="button" value="Send Request" />
            <input type="hidden" name="submitted" value="TRUE" />
            <input type="button" name="cancel" class="button" value="Cancel" />
        </form>
        <!-- Build the keyboard -->
        <div class="keyboard" id="request_form_kb">
            <div class="kb_row">
                <div class="kb_letter">Q</div>
                <div class="kb_letter">W</div>
                <div class="kb_letter">E</div>
                <div class="kb_letter">R</div>
                <div class="kb_letter">T</div>
                <div class="kb_letter">Y</div>
                <div class="kb_letter">U</div>
                <div class="kb_letter">I</div>
                <div class="kb_letter">O</div>
                <div class="kb_letter">P</div>
                <div class="kb_btn" id="kb_backspace">&larr;</div>
            </div>
            <div class="kb_row">
                <div class="kb_letter">A</div>
                <div class="kb_letter">S</div>
                <div class="kb_letter">D</div>
                <div class="kb_letter">F</div>
                <div class="kb_letter">G</div>
                <div class="kb_letter">H</div>
                <div class="kb_letter">J</div>
                <div class="kb_letter">K</div>
                <div class="kb_letter">L</div>
                <div class="kb_letter">;</div>
                <div class="kb_letter">&apos;</div>
            </div>
            <div class="kb_row" style="margin-left: 22px;">
                <div class="kb_btn" id="kb_shift">&#8679;</div>
                <div class="kb_letter">Z</div>
                <div class="kb_letter">X</div>
                <div class="kb_letter">C</div>
                <div class="kb_letter">V</div>
                <div class="kb_letter">B</div>
                <div class="kb_letter">N</div>
                <div class="kb_letter">M</div>
                <div class="kb_letter">,</div>
                <div class="kb_letter">.</div>
            </div>
            <div class="kb_row" style="margin-left: 8px;">
                <div class="kb_btn" id="kb_num">123</div>
                <div class="kb_btn" id="kb_prev">PREV</div>
                <div class="kb_space"></div>
                <div class="kb_btn" id="kb_next">NEXT</div>
            </div>
            <div class="kb_row kb_hidden" id="kb_numbers">
                <div class="kb_letter">0</div>
                <div class="kb_letter">1</div>
                <div class="kb_letter">2</div>
                <div class="kb_letter">3</div>
                <div class="kb_letter">4</div>
                <div class="kb_letter">5</div>
                <div class="kb_letter">6</div>
                <div class="kb_letter">7</div>
                <div class="kb_letter">8</div>
                <div class="kb_letter">9</div>
            </div>
            <div class="kb_row kb_hidden" id="kb_letters">
                <div class="kb_btn" id="kb_let">ABC</div>
            </div>
        </div>
    </div>
</div>