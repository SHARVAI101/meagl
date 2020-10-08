<?php 
ob_start();
include 'top.php';
$b=ob_get_contents();
ob_end_clean();

$title = "Just My Thoughts";
$buffer = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . $title . '$3', $b);

echo $buffer;

include 'dbh.php';
include_once 'functions.php';
?>

<div class="tabs">
	<a id="trendingmemes" class="sign-out-trend highlight-tab a-tab" href="#trending" style="border-radius:3px;margin-top:5px">Trending</a>
	<a id="freshmemes" class="sign-out-fresh sign-out-background-tabs a-tab" href="#fresh" style="border-radius:3px;margin-top:5px">Fresh</a>
	<?php 
	/*
	if(isset($_SESSION['id'])){
		?>
		<a id="shuffle" href="#shuffled">Shuffle</a>
		<?php
	}*/
	?>			
</div>
<div>
<?php
	echo '<div class="category-div">';
	echo '<img src="defaults/just_my_thoughts.png" style="width=200px;height:200px;" id="group_dp">';
	echo '<h4 class="group-name" style="margin-left:65px;">Just My Thoughts</h4>';

	$sql="SELECT totalSubscribers,totalMemesForThisCategory FROM memecategoriestable WHERE memeCategory='Just My Thoughts'";
	$result=mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$memeCategorySubscribers=$row['totalSubscribers'];
	$totalMemesForThisCategory=$row['totalMemesForThisCategory'];

	$memeCategorySubscribersLabel="categorySubscribeJust My Thoughts";

	echo '<p id="'.$memeCategorySubscribersLabel.'" name="'.$memeCategorySubscribersLabel.'" style="top:55px;left:180px;" class="last-active">Subscribers: '.$memeCategorySubscribers.'</p>';
	echo '<p class="created">Total Memes:'.$totalMemesForThisCategory.'</p>';
	$subscribeToMemeCategoryInnerHtml="Subscribe";
	if(isset($_SESSION['id'])){
		$userId=mysqli_real_escape_string($conn,$_SESSION['id']);
		//echo '<script language="javascript">alert("im in 1")</script>';
		$sql6= "SELECT subscribedByUserId FROM justmythoughtsmemessubscriberstable WHERE subscribedByUserId='$userId'";
		$result6=mysqli_query($conn,$sql6);
		if($row6=mysqli_fetch_assoc($result6)){//checks if the user has already liked the meme..if yes, then it will show the option to undo the like
			//echo '<script language="javascript">alert("im in 2")</script>';
			$subscribeToMemeCategoryInnerHtml="Unsubscribe";
		
		}
	}

	echo '<button type="submit" onclick="memeCategorySubscribeFunction(0,\'Just My Thoughts\',\''.$memeCategorySubscribersLabel.'\');" name="subscribecategoryJust My Thoughts" class="btn" id="userprofile-subscribe-button" style="margin: 5 auto;display:block;width:150px;position:relative">'.$subscribeToMemeCategoryInnerHtml.'</button>';
	echo '</div>';
?>
</div>
<div class="tab-content" id="tabs">

	<div class="tab trendingmemes" id="trending" style="top:420px">
		<hr>
		<div id="trending_content_div">
			<?php
			$sql="SELECT * FROM memestable WHERE (memeCategory='Just My Thoughts' AND (visibilityStatus=1 OR visibilityStatus=3)) ORDER BY likes DESC";
			$result=mysqli_query($conn,$sql);

			$total_likes=0;
			$total_memes=0;

			while($row=mysqli_fetch_assoc($result)){
				$justmythoughts_trending[]=$row;
				$total_likes+=$row['likes'];
				$total_memes+=1;
			}

			if($total_memes!=0){
			
			$avg_likes=$total_likes/$total_memes;

			$current_time=date('Y-m-d H:i:s');		
			$currentDate = DateTime::createFromFormat('Y-m-d H:i:s', $current_time);
			
			foreach($justmythoughts_trending as $key => $am){

				$memeDate = DateTime::createFromFormat('Y-m-d H:i:s', $am['datetime']);
		        $datediff=date_diff($memeDate, $currentDate);
		        			
				$differenceInTimeInHours=intval($datediff->format('%R%a'))*24;//here, we get the time in days between the two dates and then convert into an integer(since, formerly it is in string) and then convert it into hours
		        if($differenceInTimeInHours==0){
			       	$differenceInTimeInHours=0.001;
			       	//this is done to prevent division by zero in the calculation of $slopeOfLikes
			    }
				$slopeOfLikes=$am['likes']/$differenceInTimeInHours;
				
				//getting ratio of dislikes(flags for low-quality) to likes
				$imgId=$am['id'];
				$sql100="SELECT id FROM image_flags_table WHERE flagType='low-quality' AND imageId='$imgId'";
				$result100=mysqli_query($conn,$sql100);
				$numberOfDownvotes=mysqli_num_rows($result100);
				if($am['likes']!=0)
				{				
					$downvotesRatio=$numberOfDownvotes/$am['likes'];
				}else{
					$downvotesRatio=$numberOfDownvotes;
				}

				$score=($am['likes']-$avg_likes)+$slopeOfLikes-$downvotesRatio; 

				$justmythoughts_trending[$key]['trending_score']=$score;			
			}

			//removing all duplicate values
			$justmythoughts_trending = removeDuplicateArrayEntry($justmythoughts_trending, 'id');

			//sorting in descending order according to the score
			usort($justmythoughts_trending, function ($a, $b) {
			    //return $b['trending_score'] <=> $a['trending_score'];
			    if ($b['trending_score'] == $a['trending_score']) {
					return 0;
				}
				return ($b['trending_score'] < $a['trending_score']) ? -1 : 1;
			});

			//print_r($justmythoughts_trending);

			$_SESSION['justmythoughts_page_trending_counter']=0;
			$numberOfMemesInTrending=count($justmythoughts_trending);			
			//echo $numberOfMemesInTrending;
			loadMorefunction($justmythoughts_trending,$numberOfMemesInTrending,'justmythoughts_page_trending_counter','trending',"donthideform");

			echo '</div>';//closing trending_content_div

			//load more button
			if($numberOfMemesInTrending!=$_SESSION['justmythoughts_page_trending_counter'])
			{
				echo '
				<form class="load_more_memes_form" id="justmythoughts_page_trending_loadmore_form">
					<input type="hidden" name="load_more_type" value="trending">
					<input type="hidden" name="session_counter_name" value="justmythoughts_page_trending_counter">
					<input type="hidden" name="numberOfElementsInArray" value="'.$numberOfMemesInTrending.'">';?>
					<input type="hidden" name="data_array" value='<?php echo base64_encode(serialize($justmythoughts_trending)) ; ?>'>
				<?php
				echo '
					<button type="submit" class="btn">Load More</button>
				</form>
				';
			}

			}else{
				echo '</div>';//closing trending_content_div
				echo '<p class="error-message">No memes in this category yet.</p>';
			}

			?>
			<div class="add-height"></div>
	</div>

	<div class="tab freshmemes" id="fresh" style="top:420px">
		<hr>
		<div id="fresh_content_div">
		<?php
		$sql= "SELECT * FROM memestable WHERE memeCategory='Just My Thoughts' AND (visibilityStatus=1 OR visibilityStatus=3) ORDER BY id DESC";//ordering in the descending order(that is, printing all the memes in the decreasing order of their id)
		$result=mysqli_query($conn,$sql);
		
		//printing all the memes one by one
		while($row=mysqli_fetch_assoc($result)){
			$fresh_justmythoughts[]=$row;
		}

		if($fresh_justmythoughts!=0){

		$_SESSION['justmythoughts_page_fresh_counter']=0;
		$numberOfMemesInFresh=count($fresh_justmythoughts);				
		loadMorefunction($fresh_justmythoughts,$numberOfMemesInFresh,'justmythoughts_page_fresh_counter','fresh',"donthideform");

		echo '</div>';//closing fresh_content_div

		//load more button
		if($numberOfMemesInFresh!=$_SESSION['justmythoughts_page_fresh_counter'])
		{
			echo '
			<form class="load_more_memes_form" id="justmythoughts_page_fresh_loadmore_form" method="post">
				<input type="hidden" name="load_more_type" value="fresh">
				<input type="hidden" name="session_counter_name" value="justmythoughts_page_fresh_counter">
				<input type="hidden" name="numberOfElementsInArray" value="'.$numberOfMemesInFresh.'">';?>
				<input type="hidden" name="data_array" value='<?php echo base64_encode(serialize($fresh_justmythoughts)) ; ?>'>
			<?php
			echo '
				<button type="submit" class="btn">Load More</button>
			</form>
			';
		}

		}else{
			echo '</div>';//closing fresh_content_div
			echo '<p class="error-message">No memes in this category yet.</p>';
		}

		?>
		<div class="add-height"></div>		
	</div>
</div>
<div class="notifications-column categ-notification-column">
	<?php
	if(isset($_SESSION['id']))
	{
		$userId=mysqli_real_escape_string($conn,$_SESSION['id']);
		$sql="SELECT * FROM notifications_table WHERE receiverId='$userId' ORDER BY id DESC";
		$result=mysqli_query($conn,$sql);

		$areThereNotifications=false;
		$notifications_counter=0;
		echo '<h1 id="notif-heading">Notifications</h1>';
		echo '<div class="notifications-body">';
		while($row=mysqli_fetch_assoc($result)){
			$areThereNotifications=true;

			
			//only if the notification hasn't been viewed

			$notification=$row['notification'];
			$notificationId=mysqli_real_escape_string($conn,$row['id']);
			$datetime=$row['datetime'];
			$nL=$row['notificationLink'];

			$senderId=$row['senderId'];
			$sql1="SELECT profilePictureLocation FROM memberstable WHERE id='$senderId'";
			$result1=mysqli_query($conn,$sql1);
			$row1=mysqli_fetch_assoc($result1);
			$ppL=$row1['profilePictureLocation'];
			if($ppL=='')
			{
				//this means its a group ka notification
				if(preg_match_all('/\d+/', $nL, $numbers))
				{
					$groupId = end($numbers[0]);
				}
	    		$sql10="SELECT groupPicLocation FROM groups_table WHERE id='$groupId'";
				$result10=mysqli_query($conn,$sql10);
				$row10=mysqli_fetch_assoc($result10);
				$ppL=$row10['groupPicLocation'];

			}

			$viewingStatus=$row['viewingStatus'];
			if($viewingStatus==0)
			{
				$notifications_counter+=1;
				?>
				<a href="<?php echo htmlentities($nL); ?>" class="notif-link" id="notification<?php echo htmlentities($notificationId); ?>">	
				    <div class="notif">
						<img src="../<?php echo htmlentities($ppL) ?>" class="notif-dp">
					    <p class="notif-text categ-notif-text"><?php echo $notification; ?></p>							
					</div>
				</a>
				<?php
			}
			else
			{
				?>
				<a href="<?php echo htmlentities($nL); ?>" class="notif-link" id="notification<?php echo htmlentities($notificationId); ?>">	
				    <div class="notif" style="background-color:#EAEAEA">
						<img src="../<?php echo htmlentities($ppL) ?>" class="notif-dp">
					    <p class="notif-text categ-notif-text"><?php echo $notification; ?></p>							
					</div>
				</a>
				<?php
			}
		}

		if($areThereNotifications==false){
			echo '<p class="error-message">No notifications yet!</p>';			
		}else{
			?>
			<script>thereAreNotifications(<?php echo $notifications_counter; ?>);</script>
			<?php
		}
		echo "</div>";
	}	
	?>
</div>

</div>	
</body>
</head>
</html>