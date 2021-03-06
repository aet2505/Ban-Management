<?php
/*  BanManagement � 2012, a web interface for the Bukkit plugin BanManager
    by James Mortemore of http://www.frostcast.net
	is licenced under a Creative Commons
	Attribution-NonCommercial-ShareAlike 2.0 UK: England & Wales.
	Permissions beyond the scope of this licence 
	may be available at http://creativecommons.org/licenses/by-nc-sa/2.0/uk/.
	Additional licence terms at https://raw.github.com/confuser/Ban-Management/master/banmanagement/licence.txt
*/
?>
<div class="hero-unit">
	<h1>Ban Check</h1>
	<br />
	<form action="index.php" method="get" class="form-horizontal" id="search">
		<fieldset>
			<div class="control-group">
				<!-- <label class="control-label" for="servername">
					<div class="btn-group" id="searchtype">
						<button class="btn" id="player">Player</button>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li id="ip"><a href="#">IP</a></li>
						</ul>
					</div>
				</label> -->
					<div class="input-prepend">
						<div class="btn-group">
							<button id="player" class="btn dropdown-toggle" data-toggle="dropdown">
								Player
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<li id="ip"><a href="#">IP</a></li>
							</ul>
						</div>
						<input type="text" name="player" class="span4 required" placeholder="Enter Player Name">
					</div>

			</div>
		<?php
		if(!empty($settings['servers']) && count($settings['servers']) > 1) {
			echo '
			<div class="control-group">
				<label class="control-label" for="servername">Server:</label>
				<div class="controls">';
			$id = array_keys($settings['servers']);
			$i = 0;
			foreach($settings['servers'] as $server) {
				echo '
					<label class="radio">
						<input type="radio" value="'.$id[$i].'" name="server"'.($i == 0 ? ' checked="checked"' : '').' />
						'.$server['name'].'
					</label>';
				++$i;
			}
			echo '
				</div>
			</div>';
		} else if(count($settings['servers']) == 1) {
			echo '<input type="hidden" value="0" name="server" />';
		}
		?>
			<div class="form-actions">
				<input type="submit" class="btn btn-primary" value="Search" />
				<input type="hidden" name="action" value="searchplayer" />
				<a href="#" class="btn" id="viewall">View All</a>
			</div>
		</fieldset>
    </form>
</div>
<h2>Latest Bans</h2>
<?php
if(!empty($settings['servers'])) {
	echo '
	<div class="row">';
	$id = array_keys($settings['servers']);
	$i = 0;
	foreach($settings['servers'] as $server) {
		echo '
			<div class="span4">
				<h3>'.$server['name'].'</h3>
				<ul class="nav nav-tabs nav-stacked">';			
		// Clear old latest bans cache's
		clearCache($i.'/latestbans', 300);
		
		$result = cache("SELECT banned, banned_by, ban_reason, ban_expires_on FROM ".$server['bansTable']." ORDER BY ban_time DESC LIMIT 5", 300, $i.'/latestbans', $server);
		
		if(isset($result[0]) && !is_array($result[0]) && !empty($result[0]))
			$result = array($result);
		$rows = count($result);
		
		if($rows == 0)
			echo '<li>None</li>';
		else {
			$timeDiff = cache('SELECT ('.time().' - UNIX_TIMESTAMP(now()))/3600 AS mysqlTime', 5, $i, $server); // Cache it for a few seconds
		
			$mysqlTime = $timeDiff['mysqlTime'];
			$mysqlTime = ($mysqlTime > 0)  ? floor($mysqlTime) : ceil ($mysqlTime);
			$mysqlSecs = ($mysqlTime * 60) * 60;
			foreach($result as $r) {
				$expires = ($r['ban_expires_on'] + $mysqlSecs)- time();
				echo '<li class="latestban"><a href="index.php?action=viewplayer&player='.$r['banned'].'&server='.$i.'">'.$r['banned'].'</a><button class="btn btn-info" rel="popover" data-html="true" data-content="'.$r['ban_reason'].'" data-original-title="'.$r['banned_by'];
				if($r['ban_expires_on'] == 0)
					echo ' <span class=\'label label-important\'>Never</span>';
				else if($expires > 0)
					echo ' <span class=\'label label-warning\'>'.secs_to_hmini($expires).'</span>';
				else
					echo ' <span class=\'label label-success\'>Now</span>';
				echo '"><i class="icon-question-sign icon-white"></i></button></li>';
			}
		}
		
		echo '
				</ul>
			</div>';
		++$i;
	}
	echo '
	</div>';
} else
	echo '<p>None</p>';
?>