<?php

ob_start();
include 'top.php';
$b=ob_get_contents();
ob_end_clean();

$title = "My Home";
$buffer = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . $title . '$3', $b);

echo $buffer;

include 'dbh.php';
include_once 'functions.php';
//$startTime = microtime(true);
date_default_timezone_set('Asia/Kolkata');//setting the timezone
//checking if the user that is trying to view this profile page is the one whose profile page it is or if it is someone else who is trying to view his profile page
$userId = isset($_GET['id']) ? mysqli_real_escape_string($conn,$_GET['id']) : mysqli_real_escape_string($conn,$_SESSION['id']);//this "userId" is the variable that stores the id of the person whose profile page the current user is trying to access
//echo $_SESSION['id'];
/*if(isset($_GET['id']) && isset($_SESSION['id'])){		

	if($_GET['id']==$_SESSION['id']){
		//~~~~`edit personal information~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		?>
		<a href="editPersonalInfo.php" style="margin-top:50px;">Edit Personal Information</a><br>	
		<?php
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~`
	}
}*/
/*$user_ip = getenv('REMOTE_ADDR');
$geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
$country = $geo["geoplugin_countryName"];
$city = $geo["geoplugin_city"];
echo $user_ip;
echo $country;
echo $city;*/

if(isset($_SESSION['id']) && $userId==$_SESSION['id']){
	//if the person is trying to access his own profile
	$username=$_SESSION['username'];
	//$memesUploaded=$_SESSION['memesUploaded'];
	//$numberofSubscribers=$_SESSION['numberofSubscribers'];
	$sql1= "SELECT * FROM memberstable WHERE id='$userId'";
	$result1=mysqli_query($conn,$sql1);
	$row=mysqli_fetch_assoc($result1);
	$name=$row['name'];
	$username=$row['username'];
	$memesUploaded=$row['memesUploaded'];
	$numberofSubscribers=$row['numberofSubscribers'];
	$numberOfQuestionsAsked=$row['numberOfQuestionsAsked'];
	$points=$row['points'];
	$status=$row['userStatus'];
	$otherChannelSubscriptions=$row['otherChannelSubscriptions'];

	//~~~~~~~~~~~~~~~~~~~IMPORTANT- this logout form has 34 SQL Injection vulnerabilites~~~~~~~~~~~~~~~~~~~~~~~
	/*echo "<form name='logoutForm' method='POST' action='logout.php' style='margin-top:40px;'>
	      <button class='outbutton' >LOG OUT</button>
	      </form>
		";*/
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		echo '<div class="infobox centering">';
		echo '<div class="left-part">';
			echo '<img src="../'.$row['profilePictureLocation'].'" id="infobox-dp" style="padding:5px"><br>';
			echo "<h1 class='username'>".htmlentities($username)."</h1>";
			//echo "<h1 class='score'> Score|".floor($points)."</h1>";			
}else{
	
	//if the person is trying to access someone else's profile
	$sql1= "SELECT * FROM memberstable WHERE id='$userId'";
	$result1=mysqli_query($conn,$sql1);
	$row=mysqli_fetch_assoc($result1);
	$name=$row['name'];
	$username=$row['username'];
	$memesUploaded=$row['memesUploaded'];
	$numberofSubscribers=$row['numberofSubscribers'];
	$numberOfQuestionsAsked=$row['numberOfQuestionsAsked'];
	$points=$row['points'];
	$status=$row['userStatus'];
	$otherChannelSubscriptions=$row['otherChannelSubscriptions'];
	echo '<div class="infobox centering">';
	echo '<div class="left-part">';
		echo '<img src="../'.$row['profilePictureLocation'].'" id="infobox-dp" style="padding:5px">';
		echo "<h1 class='username'>".htmlentities($username)."</h1>";
		//echo "<h1 class='score'> Score|".floor($points)."</h1>";
}

if(!isset($numberofSubscribers)){
	$numberofSubscribers=0;
	//echo "<p> Subscribers: ".$numberofSubscribers."</p>";
}
$subscribeLabel="subscribers0";//since this subscribers button is on the userprofiles page, we are keeping imageId as 0
$imageId=0;
$subscribeToUserInnerHtml="Subscribe";
if(isset($_SESSION['id'])){
	//echo '<script language="javascript">alert("im in 1")</script>';
	$sql2= "SELECT subscribedById FROM subscriberstable WHERE uploaderId='$userId'";
	$result2=mysqli_query($conn,$sql2);
	while($row2=mysqli_fetch_assoc($result2)){//checks if the user has already liked the meme..if yes, then it will show the option to undo the like
		if($row2['subscribedById']==$_SESSION['id']){
			//echo '<script language="javascript">alert("im in 2")</script>';
			$subscribeToUserInnerHtml="UnSubscribe";
		}
	}
}



//subscribers info
echo '<div id="user-subs-div">';
echo '  <p class="info-subscribers">Subscribers:</p>
	 	<p id="'.$subscribeLabel.'" name="'.$subscribeLabel.'" class="info-subscribers-number">'.$numberofSubscribers.'</p>';
echo '</div>';
echo '<button type="submit" onclick="subscribeFunction(\''.$imageId.'\',\''.htmlentities($userId).'\',\''.$subscribeLabel.'\',\''.htmlentities($username).'\');" name="subscribe'.htmlentities($userId).'" class="btn" id="userprofile-subscribe-button">'.$subscribeToUserInnerHtml.'</button>';
//






echo "</div>";//closing left-part div

echo "<div class='right-part' style='display:none'>";
echo '<hr>';
echo '<br>';
echo "<h1 class='usernick'>".htmlentities($name)."</h1>";
echo "<p class='user-says'><q>".$status."</q></p>";
echo "<p class='info-subscribed' id='user-info-subscribed'>Subscriptions: ".$otherChannelSubscriptions."</p>";
echo "<p class='info-posts'>Posts: ".$memesUploaded."</p>";
echo "<p class='info-questions'>Questions: ".$numberOfQuestionsAsked."</p>";


//~~~~~~~~~~~FRIENDSHIP STATUS PART~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//checking if the user is checking someone else's account in order to display the proper friend details
if(isset($_SESSION['id'])){
	//only if the user is logged in will the friendship status and options be displayed
	if(isset($_GET['id']) && $_GET['id']!=$_SESSION['id']){
		//~~~~~~~~~~~FRIENDSHIP STATUS PART~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		//only if user is checking someone else's account will the friendship status and options be displayed
		//here it is important to note that $userId will be $_GET['id'] only
		
		$isRelationship=false;
		$sql="SELECT receiver_user_id,relation,id  FROM friends_table WHERE sender_user_id='$userId'";
		$result=mysqli_query($conn,$sql);
		while($row=mysqli_fetch_assoc($result)){
			if($row['receiver_user_id']==$_SESSION['id']){
				//There is some relationship between these two users
				$isRelationship=true;
				$currentUserStatus="friendRequestReceiver";
				$relation=$row['relation'];
				$friend_request_id=$row['id'];
				break;
			}
		}
		if($isRelationship==false){
			//If no relation is found in the first query only then execute this block
			$sql="SELECT sender_user_id,relation,id FROM friends_table WHERE receiver_user_id='$userId'";
			$result=mysqli_query($conn,$sql);
			while($row=mysqli_fetch_assoc($result)){
				if($row['sender_user_id']==$_SESSION['id']){
					//There is some relationship between these two users(two users=the user whose profile is being viewed and the one who is viewing it)
					$isRelationship=true;	
					$currentUserStatus="friendRequestSender";					
					$relation=$row['relation'];
					$friend_request_id=$row['id'];
					break;
				}
			}
		}

		if(isset($relation)){
			//If there is some relationship between these two users
			//echo "relation";
			if($currentUserStatus=="friendRequestSender"){
				if($relation==0){
					echo "<button class='btn friend-request'>Friend Request Sent</button>";
				}
				else if($relation==1){
					echo "<button class='btn friend-request' id='friends'>Friends</button>";//if the user has approved the friend request
				}
				else if($relation==2){
					echo "<p>Relation Status: You have been BLOCKED by this user!</p>";
				}
			}
			else{
				if($relation==0){
					
					//echo "<button class='btn friend-request'>Accept Friend Request</button>";
					//echo "<h1>Relation Status: This user's Friend Request is PENDING APPROVAL by you!</h1>";
					$sender_user_id=mysqli_real_escape_string($conn,$_GET['id']);
					$receiverId=mysqli_real_escape_string($conn,$_SESSION['id']);
					?>
					<form class="acceptfriendrequestform" id="acceptfriendrequestform<?php echo htmlentities($friend_request_id);?>" name="userprof_top_friend_req_btn">
						<input type="hidden" name="sender_user_id" value="<?php echo htmlentities($sender_user_id); ?>">
						<input type="hidden" name="receiver_user_id" value="<?php echo htmlentities($receiverId); ?>">
						<input type="hidden" name="sender_username" value="<?php echo htmlentities($username); ?>">
						<button type="submit" class="acceptFriendRequestButton btn friend-request" id="<?php echo htmlentities($friend_request_id); ?>" style="color:white">Accept Friend Request</button>
					</form>
					<button class='btn friend-request' id='friends' style="display:none">Friends</button>
					<?php
				}
				else if($relation==1){
					echo "<button class='btn friend-request' id='friends'>Friends</button>";//if the user has approved the friend request
				}
				else if($relation==2){
					echo "<button class='btn friend-request'>User Blocked</button>";
				}
			}
			
		}
		else{
			//There's no relationship between theese two people
			//send friend request code
			//echo "no relation<br>";
			echo "<p class='friendrequeststatus'></p>";
			echo '<form class="sendfriendrequestform">
					<input type="hidden" name="sender" value="'.htmlentities($_SESSION['id']).'">
					<input type="hidden" name="receiver" value="'.htmlentities($_GET['id']).'">
				  	<button class="btn" id="friend-request" type="submit">Send Friend Request!</button>
				  </form>';

		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		}
		
	}

}
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
/*
echo "<h1 class='score'> Score|".floor($points)."</h1>";*/
echo '</div>';//closing right-part div
echo'
<p id="more-info-chevron" style="cursor:pointer">
    <span class="glyphicon glyphicon-chevron-down"></span>
</p>
';
echo '</div>';//closing class="info centering" div box
echo '<hr style="margin-top:-15px">';
echo '<h1 name="Error'.$imageId.'" class="error-message"></h1>';

/*
if(isset($_SESSION['id']) && isset($_GET['id']) && $_GET['id']==$_SESSION['id']){
		
	//~~~~~~~~~~~~~~INVITE FRIENDS PART~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	?>
	<div class="invitation">
		<hr>
		<h3 class="invitation-header">Meagl is fun with friends! Invite them.</h3>

		<form class="inviteToMeagl" action="" method="POST">
			<input type="text" class="form-control" id="inviteToMeaglTextBox" name="email" placeholder="Friends Email"><br>
			<button type="submit" class="inviteToMeaglButton btn">Invite to Meagl!</button>
		</form>

		<p class="score-prompt" id="cp">Actually, it is more than just fun. You get 40 points if they register!</p>	
		<p id="inviteToMeaglMessage"></p>
		<hr>
	</div>
	<?php
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
*/
/*if(!isset($_SESSION['id']))
{
	echo "<br><br>";
}*/

?>

	<?php
	if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
		//only visible if the user is viewing his own profile 
	?>
	<div class="tabs" >
		<a href="#memes" id="memes-tab" class="highlight-tab a-tab">Memes</a>
		<a href="#subscribedcontent" id="subscriptions-tab" class="background-tabs a-tab">Subscriptions</a>
		<a href="#sociallife" id="social-tab" class="background-tabs a-tab">Social Life</a>
	<?php
	}
	else{
		if(isset($_SESSION['id']))
		{
			echo '<div class="tabs">
					<a href="#memes" id="memes-tab" class="highlight-tab a-tab">Memes</a>
					<a href="#subscribedcontent" id="subscriptions-tab" class="background-tabs a-tab">Subscriptions</a><br><br>';
		}
		else
		{
			echo '<div class="tabs">
					<a href="#memes" id="memes-tab" class="highlight-tab a-tab">Memes</a>
					<a href="#subscribedcontent" id="subscriptions-tab" class="background-tabs a-tab">Subscriptions</a><br><br>';
		}
	
	}
	?>
	<!--<canvas class="homepage-tab-bottom" id="tab-bottom" width="600" height="3"></canvas>-->
	
</div>

<div class="tab-content">
		
		<?php
		//if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
				//only visible if the user is viewing his own profile
		?>
		<div class="tab memes-tab-content" id="memes">	
			<h2 class="userprofile-tab-heading">MEMES</h2>		
		<div id="my_memes_content_div">
		<?php
		/*
		}
		else{
			if(isset($_SESSION['id']))
			{
				echo '<div class="tab memes-tab-content" id="memes" style="right:350px;margin-top:50px">
					<div id="my_memes_content_div">';
			}
			else
			{
				echo '<div class="tab memes-tab-content" id="memes" style="right:180px;margin-top:100px">
					<div id="my_memes_content_div">';
			}
		}*/
		//printing out all the memes uploaded by the user
		//echo $memesUploaded;
		if($memesUploaded>=1){
			$sql= "SELECT * FROM memestable WHERE uploader='$username' ORDER BY id DESC";//ordering in the descending order(that is, printing all the memes in the decreasing order of their id)
			$result=mysqli_query($conn,$sql);
			//printing all the memes one by one
			
			while($row=mysqli_fetch_assoc($result)){
				$mymemes[]=$row;
			}

			$_SESSION['my_memes_counter']=0;
			$numberOfMemesInMymemes=count($mymemes);		
			//echo $numberOfMemesInMymemes;
			loadMorefunction($mymemes,$numberOfMemesInMymemes,'my_memes_counter','my_memes',"donthideform");

			echo '</div>';//closing mymemes_content_div
			//load more button
			if($numberOfMemesInMymemes!=$_SESSION['my_memes_counter'])
			{
				echo '
				<form class="load_more_memes_form" id="my_memes_loadmore_form">
					<input type="hidden" name="load_more_type" value="my_memes">
					<input type="hidden" name="session_counter_name" value="my_memes_counter">
					<input type="hidden" name="numberOfElementsInArray" value="'.$numberOfMemesInMymemes.'">';?>
					<input type="hidden" name="data_array" value='<?php echo base64_encode(serialize($mymemes)) ; ?>'>
				<?php
				echo '
					<button type="submit" class="btn load-more">Load More</button>
				</form>
				';
			}
						
		}
		else{
			//that is if the user has uploaded no memes
			if(isset($_SESSION['id']) && $userId==$_SESSION['id']){
				//if user is accessing his own profile
				echo '<p class="error-message">You have not uploaded any memes!</p>';
				echo '<div class="add-height"></div>';
			}
			else{
				//if user is accessing someone else's profile
				echo '<p class="error-message">This user has not uploaded any memes.</p>';
				echo '<div class="add-height"></div>';
			}
			echo '</div>';//closing mymemes_content_div
		}
		?>
	</div>
	
		<?php
		//if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
			//only visible if the user is viewing his own profile
			echo '<div class="tab subscriptions-tab-content" id="subscribedcontent">';
		?>
		<?php
		/*}
		else
		{
			if(isset($_SESSION['id']))
			{
				echo '<div class="tab subscriptions-tab-content" id="subscribedcontent" style="right:350px;margin-top:50px">';
			}
			else
			{
				echo '<div class="tab subscriptions-tab-content" id="subscribedcontent" style="right:180px;margin-top:100px">';
			}
		}*/

		?>
		<h2 class="userprofile-tab-heading" style="width:220px">SUBSCRIPTIONS</h1>
		<?php
		/*
		?>		
		<ul class="inner-tabs">	
			<?php
			if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
				//only visible if the user is viewing his own profile
			?>
			<li class="all-content"><a class="all-content-link" href="#allcontent" style="color: #aaa;">All latest content</a></li>
			<?php
			}
			?>
			<li class="subscribed-channels"><a class="subscribed-channels-link" href="#subscribedchannels" style="color: #aaa;">Subscribed Channels</a></li>
			<?php
			if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
				//only visible if the user is viewing his own profile
			?>			
			<li class="subscribed-categories"><a class="subscribed-categories-link" href="#subscribedcategories" style="color: #aaa;">Subscribed Categories</a></li>
			
			<?php
			}
			?>
			
			
		</ul>
		<?php
		*/
		?>
		<div class="inner-tab-content">
			<br>
			<div class="inner-tab" id="subscribedchannels">
				<?php
				if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
					//only visible if the user is viewing his own profile
					$uId=$_SESSION['id'];
				}else{
					$uId=$_GET['id'];
				}
				$sql="SELECT uploaderId FROM subscriberstable WHERE subscribedById='$uId'";
    			$result=mysqli_query($conn,$sql);
    			$noSubscribedChannels=true;
    			while($row=mysqli_fetch_assoc($result)){
    				$noSubscribedChannels=false;//this user has subscribed to meme-channels
    				$channelId=mysqli_real_escape_string($conn,$row['uploaderId']);
    				/*$sql1="SELECT * FROM memberstable WHERE id='$channelId'";
    				$result1=mysqli_query($conn, $sql1);
    				$channelId=$row['uploaderId'];*/
    				$sql2="SELECT profilePictureLocation FROM memberstable WHERE id='$channelId'";
    				$result2=mysqli_query($conn, $sql2);
    				$row1=mysqli_fetch_assoc($result2);
    				$ppL=$row1['profilePictureLocation'];
    				echo '<a href="userprofile.php?id='.$channelId.'"><img src="../'.$ppL.'" class="subscription-image" ></a>';
    				/*echo '<p class="error-message"><a href="userprofile.php?id='.htmlentities($channelId).'">'.$channelName.'</a></p>';
    				echo '<p class="error-message" class="error-message">Subscribers: '.htmlentities($numberofSubscribers).'</p>';
    				echo '<hr>';*/
    			}
    			if($noSubscribedChannels==true){
    				echo '<p class="error-message">No memes to display here yet!</p>';
    			}
				?>
			</div>

			<hr >
			<?php
			
			
			if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
				//only visible if the user is viewing his own profile

			/*
			//no subscribed categories part in mobile website
			?>
			<div class="inner-tab" id="subscribedcategories">
				<!--<div id="subscribedcategories-content-div">-->
				<?php
				$allmemes_categories = array();//this array stores all the memes from all the channels and the meme categories that this user has subscribed to
				$userId=mysqli_real_escape_string($conn,$_GET['id']);
    			//$startTime = microtime(true);
    			//getting memes from subscribed meme categories
    			$tablesArray = array("savagememessubscriberstable", "celebmemessubscriberstable", "gamingmemessubscriberstable", "justmythoughtsmemessubscriberstable", "sportsmemessubscriberstable", "politicsmemessubscriberstable", "othermemessubscriberstable","comicmemessubscriberstable","collegememessubscriberstable");
    			$memeCategoriesTable= array("Savage","Celeb","Gaming","Just My Thoughts", "Sports", "Politics", "Other", "Comics", "College / School");
    			//print_r($tablesArray);
    			foreach ($tablesArray as $tablename ) {
    				$sql2="SELECT subscribedByUserId FROM $tablename WHERE subscribedByUserId='$userId'";
	    			$result2=mysqli_query($conn,$sql2);
	    			while($row2=mysqli_fetch_assoc($result2)){
	    				$position = array_search($tablename, $tablesArray);
	    				$memeCategory=$memeCategoriesTable[$position];
	    				$sql3="SELECT * FROM memestable WHERE memeCategory='$memeCategory' AND uploaderId!='$userId' ORDER BY datetime DESC";
	    				$result3=mysqli_query($conn, $sql3);
	    				while($row3=mysqli_fetch_assoc($result3)){
		    				//echo $row1['uploader'];
		    				$allmemes_categories[]=$row3;
	    				}
    				}
    			}
    			//echo "Time:  " . number_format(( microtime(true) - $startTime), 10) . " Seconds\n";
    			//print_r($allmemes_categories);
    			if (!empty($allmemes_categories)) {
	    			$allmemes_categories = array_map("unserialize", array_unique(array_map("serialize", $allmemes_categories)));//removes all duplicate entries(same entries entered multiple times)
	    			arsort($allmemes_categories);//sorts by placing latest content first

	    			//traversing thorugh the array and printing all the memes
	    			$_SESSION['subscribedcategories_counter']=0;
					$numberOfMemesInSubscribedCategories=count($allmemes_categories);		
					
					//echo serialize($allmemes_categories);
					loadMorefunction($allmemes_categories,$numberOfMemesInSubscribedCategories,'subscribedcategories_counter','subscribedcategories',"donthideform");

					//echo '</div>';//closing subscribedcategories-content-div

					//load more button
					if($numberOfMemesInSubscribedCategories!=$_SESSION['subscribedcategories_counter'])
					{
						echo '
						<form class="load_more_memes_form" id="subscribedcategories_loadmore_form">
							<input type="hidden" name="load_more_type" value="subscribedcategories">
							<input type="hidden" name="session_counter_name" value="subscribedcategories_counter">
							<input type="hidden" name="numberOfElementsInArray" value="'.$numberOfMemesInSubscribedCategories.'">';?>
							<input type="hidden" name="data_array" value='<?php echo base64_encode(serialize($allmemes_categories)) ; ?>'>
						<?php
						echo '
							<button type="submit" class="btn load-more">Load More</button>
						</form>
						';
					}
	    		}
	    		else{
	    			echo '<p class="error-message">No memes to display here yet!</p>';
	    		}
    			?>
			</div>
			*/
			?>

			<div class="inner-tab" id="allcontent">
				<h2 class="userprofile-tab-heading" style="width:200px;font-size:20px;margin-top:2px">ALL LATEST CONTENT</h1>
				<?php
				$allmemes = array();//this array stores all the memes from all the channels and the meme categories that this user has subscribed to
				//getting memes from subscribed channels				
				$sql="SELECT uploaderId FROM subscriberstable WHERE subscribedById='$userId'";
    			$result=mysqli_query($conn,$sql);
    			while($row=mysqli_fetch_assoc($result)){
    				$channelId=$row['uploaderId'];
    				$sql1="SELECT * FROM memestable WHERE uploaderId='$channelId' ORDER BY datetime DESC";
    				$result1=mysqli_query($conn, $sql1);
    				while($row1=mysqli_fetch_assoc($result1)){
    				//echo $row1['uploader'];
    				$allmemes[]=$row1;
    				}
    			}
    			//$startTime = microtime(true);
    			//getting memes from subscribed meme categories
    			$tablesArray = array("savagememessubscriberstable", "celebmemessubscriberstable", "gamingmemessubscriberstable", "justmythoughtsmemessubscriberstable", "sportsmemessubscriberstable", "politicsmemessubscriberstable", "othermemessubscriberstable","comicmemessubscriberstable","collegememessubscriberstable");
    			$memeCategoriesTable= array("Savage","Celeb","Gaming","Just My Thoughts", "Sports", "Politics", "Others", "Comics", "College / School");
    			//print_r($tablesArray);
    			foreach ($tablesArray as $tablename ) {
    				//print_r($tablename);
    				$sql2="SELECT subscribedByUserId FROM $tablename WHERE subscribedByUserId='$userId'";
	    			$result2=mysqli_query($conn,$sql2);
	    			while($row2=mysqli_fetch_assoc($result2)){
	    				$position = array_search($tablename, $tablesArray);
	    				$memeCategory=$memeCategoriesTable[$position];
	    				$sql3="SELECT * FROM memestable WHERE memeCategory='$memeCategory' AND uploaderId!='$userId' ORDER BY datetime DESC";
	    				$result3=mysqli_query($conn, $sql3);
	    				while($row3=mysqli_fetch_assoc($result3)){
		    				//echo $row1['uploader'];
		    				$allmemes[]=$row3;
	    				}
    				}
    			}
    			//echo "Time:  " . number_format(( microtime(true) - $startTime), 10) . " Seconds\n";
    			
    			$allmemes = array_map("unserialize", array_unique(array_map("serialize", $allmemes)));//removes all duplicate entries(same entries entered multiple times)
    			arsort($allmemes);//sorts by placing latest content first
    			//traversing thorugh the array and printing all the memes
    			if (!empty($allmemes)) {

    				$_SESSION['all_subscribed_content_counter']=0;
					$numberOfMemesInAllSubscribedContent=count($allmemes);		
					//echo serialize($allmemes);
					loadMorefunction($allmemes,$numberOfMemesInAllSubscribedContent,'all_subscribed_content_counter','allSubscribedContent',"donthideform");

					//echo '</div>';//closing AllSubscribedContent_content_div

					//load more button
					if($numberOfMemesInAllSubscribedContent!=$_SESSION['all_subscribed_content_counter'])
					{
						echo '
						<form class="load_more_memes_form" id="all_subscribed_content_loadmore_form">
							<input type="hidden" name="load_more_type" value="allSubscribedContent">
							<input type="hidden" name="session_counter_name" value="all_subscribed_content_counter">
							<input type="hidden" name="numberOfElementsInArray" value="'.$numberOfMemesInAllSubscribedContent.'">';?>
							<input type="hidden" name="data_array" value='<?php echo base64_encode(serialize($allmemes)) ; ?>'>
						<?php
						echo '
							<button type="submit" class="btn load-more">Load More</button>
						</form>
						';
					}

	    		}
	    		else{
	    			echo '<p class="error-message">No memes to display here yet!</p>';
	    		}
    			?>
			</div>
			<?php
			}
			?>
		</div>	
		<div class="add-height"></div>

	</div>

	<?php
	if((isset($_GET['id']) && isset($_SESSION['id']) && $_GET['id']==$_SESSION['id'])){
		//only visible if the user is viewing his own profile
	?>
	<div class="tab" id="sociallife">
		<h2 class="userprofile-tab-heading" style="width:168px">SOCIAL LIFE</h1>
		<div class="friends_div">			
			<h3 class="friend-list-header" style="width:140px">My Friends</h3>
			<div class="all_friends_div" style="display:none">
				<?php
				//make friends div
				$userId=mysqli_real_escape_string($conn,$_SESSION['id']);
				//fetching the friends' list and friend requests
				$sql="SELECT * FROM friends_table WHERE sender_user_id='$userId' OR receiver_user_id='$userId'";
				$result=mysqli_query($conn,$sql);
				
				$friends=false;//variable to check if a user has atleast one friend or not
				
				while($row=mysqli_fetch_assoc($result)){
					
					$sender_user_id=$row['sender_user_id'];
					$receiver_user_id=$row['receiver_user_id'];
					$friend_request_id=$row['id'];
					if($row['relation']==1){
						?>
						<div class="friend" id="friend<?php echo $friend_request_id; ?>">
							<?php
							//so the two users are friends ..now checking which one of them is the current user user whose profile is being accessed and which one is his/her friend
							$friends=true;
							if($sender_user_id==$userId){
								//the current user is the sender therfore, his friend is the receiver
								$sql1="SELECT * FROM memberstable WHERE id='$receiver_user_id'";				
								$friend_id=$receiver_user_id;
							}
							else{
								//the current user is the receiver therfore, his friend is the sender
								$sql1="SELECT * FROM memberstable WHERE id='$sender_user_id'";
								$friend_id=$sender_user_id;
							}
							$result1=mysqli_query($conn,$sql1);
							$row1=mysqli_fetch_assoc($result1);
							echo "<p class='friends'><a style='color:black' href=userprofile.php?id=".htmlentities($friend_id).">".$row1['username']."</a>
								  ";

							echo '<button type="submit" class="removefriendbutton btn" onclick="removefriendFunction(\''.htmlentities($friend_request_id).'\');" id="removefriend'.htmlentities($friend_request_id).'" name="removefriend'.htmlentities($friend_request_id).'">Unfriend</button></p>
								 ';

						?>
						</div>
						<?php
					}
					
				}
				if($friends==false){
					echo '<p class="friends error-message">No friends yet!</p> ';
				}		
				?>
			</div>
			<p id="show-friends-chevron" style="cursor:pointer">
			    <span class="glyphicon glyphicon-chevron-down"></span>
			</p>
		</div>
		<?php

		?>	

		<div class="social-tab-content">
			<?php
			/*
			?>
			<div class="search-social">
				<form class="userprofile_username_search" action="usernamesearch.php" method="POST">
					<input type="textarea" class="search-people form-control" name="search" id="searchtext" placeholder="Search Users"><br>
					<input type="submit" value="Search" class="usersearchbutton btn">
				</form>
				
				<div class="searchusers" id="searchusers" style="display:none">
					<p id="usersearchMessage"></p>
					<div id="usersearchresultsdiv"></div>
				</div>
				<button class="searchtogglebutton btn" style="display:none"><span class="glyphicon glyphicon-remove"></span></button>
			</div>
			<?php 
			*/
			?>
		<?php
		echo '<button class="showMakeNewGroupFormButton btn create-group">Create New Group</button>';
		?>
		<div class="cover-page">
		<div class="make_group_div" id="make_group_div">
			<p class="create-group-header">Create Group</p>
			<i class="fa fa-times close-group" aria-hidden="true" style="font-size:21px"></i>
			<form class="newGroupCreationForm" method="post" action="newFriendsGroupCreation.php" style="margin-top:-25px">
				<p class="group-name-header" style="margin-left:-30px"> Group Name:</p>
				<input id="groupNameTextbox" class="group_input form-control" style="display:none;margin-top:-8px;margin-bottom:10px" type="text" name="groupname" placeholder="Enter groupname">
				<p class="group-name-header-1" id="invite-prompt" style="margin-left:-30px">Send invites:</p> 
				<input class="group_input" type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
				<input class="group_input" type="hidden" name="datetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
				<input class="group_input" type="hidden" name="inviterId" value="<?php echo htmlentities($userId) ?>">
				<input class="group_input" type="hidden" name="inviterUserName" value="<?php echo htmlentities($_SESSION['username']); ?>">
				
				<?php
				//make friends div
				//fetching the friends' list and friend requests
				$sql="SELECT id,sender_user_id,receiver_user_id,relation FROM friends_table WHERE sender_user_id='$userId' OR receiver_user_id='$userId'";
				$result=mysqli_query($conn,$sql);
				
				$friends=false;//variable to check if a user has atleast one friend or not
				?>
				<input type="text" class="friend-prompt form-control" id="friends_name_form" placeholder="Friend's name" style="margin-left:15px;margin-top:-8px">
				<div class="friend-list" style="margin-left:15px;max-height:200px">
				<div class="checkbox" id="friends_names_autocomplete_suggestions">		
				<?php
				$counter=0;
				while($row=mysqli_fetch_assoc($result)){
					
					$sender_user_id=mysqli_real_escape_string($conn,$row['sender_user_id']);
					$receiver_user_id=mysqli_real_escape_string($conn,$row['receiver_user_id']);
					$friend_request_id=mysqli_real_escape_string($conn,$row['id']);
					if($row['relation']==1){
						?>		
						<script>showGroupElements();//this function displays group_name input and other statements that is,only if he has friends will the make group form be shown...otherwise it wont</script>			
						<?php
						//so the two users are friends ..now checking which one of them is the current user user whose profile is being accessed and which one is his/her friend
						$friends=true;
						if($sender_user_id==$userId){
							//the current user is the sender therfore, his friend is the receiver
							$sql1="SELECT username FROM memberstable WHERE id='$receiver_user_id'";				
							$friend_id=$receiver_user_id;
						}
						else{
							//the current user is the receiver therfore, his friend is the sender
							$sql1="SELECT username FROM memberstable WHERE id='$sender_user_id'";
							$friend_id=$sender_user_id;
						}
						$result1=mysqli_query($conn,$sql1);
						$row1=mysqli_fetch_assoc($result1);

						if($counter==0)
						{
						?>
						<label class="checkbox"><input type="checkbox" class="groupInviteCheckbox" name="new_group_user_ids[]" value="<?php echo htmlentities($friend_id); ?>"><?php echo htmlentities($row1['username']); ?></label>
						<?php
						}
						else{
						?>
						<label class="checkbox"><input type="checkbox" class="groupInviteCheckbox" name="new_group_user_ids[]" value="<?php echo htmlentities($friend_id); ?>"><?php echo htmlentities($row1['username']); ?></label>
						<?php
						}
						?>
						<hr>
						<?php
						$counter+=1;					
					}					
				}
				?>
				</div>
			</div>
				<?php
				if($friends==false){
					echo '<p class="friends error-message">You can\'t make a Meme Share Group if you don\'t have friends!</p> ';
				}	
				else{
					?>
					<button class="btn group_input" type="submit" style="margin-left:15px;margin-top:5px">Create <span class="glyphicon glyphicon-chevron-right"></span></button>
					<?php
				}					
				?>
				
			</form>
			<p class="group_input" id="groupMakingOutput"></p>
			
		</div>
		</div>
		<hr>
		<?php

		//echo '<button class="friendrequeststogglebutton">View Friend Requests</button>';
		?>

		<div class="open-friend-request-div">	
			<h3 class="friend-request-heading">Friend Requests</h3>
			<button class="friendrequeststogglebutton btn"><span class="glyphicon glyphicon-menu-down" style="position:relative"></span></button>
		</div>

		<div class="friendrequests" style="display:none">
			<?php

			$no_friend_requests=true;

			$sql="SELECT id,sender_user_id,relation FROM friends_table WHERE receiver_user_id='$userId'";
			$result=mysqli_query($conn,$sql);
			while($row=mysqli_fetch_assoc($result)){			
				//There are some friend requests for this user
				

				$relation=$row['relation'];
				
				if($relation==0){
					$no_friend_requests=false;
					//a friend request was sent to this user by someone else
					$sender_user_id=$row['sender_user_id'];
					$friend_request_id=$row['id'];
					$sql1="SELECT * FROM memberstable WHERE id='$sender_user_id'";
					$result1=mysqli_query($conn,$sql1);
					$row1=mysqli_fetch_assoc($result1);
					$sender_username=$row1['username'];
					echo '<hr style="display:block;margin:0 auto;width:300px">';
					echo '<div class="request">';
					echo "<p class='acceptOrRejectfriendrequeststatus request-content' id='friendrequeststatus".htmlentities($friend_request_id)."'><a href='userprofile.php?id=".htmlentities($sender_user_id)."' style='color:b522a8'>".$sender_username."</a> has sent you a friend request!</p>"; 
					?>

					<form class="acceptfriendrequestform" id="acceptfriendrequestform<?php echo htmlentities($friend_request_id);?>">
						<input type="hidden" name="sender_user_id" value="<?php echo htmlentities($sender_user_id); ?>">
						<input type="hidden" name="receiver_user_id" value="<?php echo htmlentities($userId); ?>">
						<input type="hidden" name="sender_username" value="<?php echo htmlentities($sender_username); ?>">
						<button type="submit" class="acceptFriendRequestButton btn accept text-success" id="<?php echo htmlentities($friend_request_id); ?>"><span class="glyphicon glyphicon-ok"></span></button>
					</form>
					<form class="rejectfriendrequestform" id="rejectfriendrequestform<?php echo htmlentities($friend_request_id);?>">
						<input type="hidden" name="sender_user_id" value="<?php echo htmlentities($sender_user_id); ?>">
						<input type="hidden" name="receiver_user_id" value="<?php echo htmlentities($userId); ?>">
						<input type="hidden" name="sender_username" value="<?php echo htmlentities($sender_username); ?>">
						<button type="submit" class="rejectFriendRequestButton btn reject text-danger" id="<?php echo htmlentities($friend_request_id); ?>"><span class="glyphicon glyphicon-remove"></span></button>
					</form>
					
					<?php
					echo '</div>';					
				}

			}

			if($no_friend_requests==true){
				echo '<p class="error-message" style="display:block;width:300px;margin:0 auto">No pending friend requests to be displayed!</p>';
			}

			?>
			
		</div>
		<hr>
		<?php		
		//echo '<button class="groupInvitesToggleButton">View Group Invites</button>';
		?>			

		<div class="open-friend-request-div" style="width:230px">
			<h3 class="group-request-heading">Group Invites</h3>
			<button class="groupInvitesToggleButton btn"><span class="glyphicon glyphicon-menu-down" style="position:relative;top:-2px;left:-3.5px;"></span></button> 
		</div>

		<div class="view_pending_group_invites" style="display:none">						
			<?php
			$userId=mysqli_real_escape_string($conn,$_SESSION['id']);
			$groupInvites=false;//false if the user has no group invites and true if the user does have group invites
			

			$sql="SELECT id,groupId,invitationDate,inviterUserName,inviterId,groupname FROM group_participants_table WHERE participantId='$userId' AND invitationStatus=0";
			$result=mysqli_query($conn,$sql);
			while($row=mysqli_fetch_assoc($result)){
				$groupInvites=true;

				$groupId=$row['groupId'];
				$inviteId=$row['id'];
				$date=$row['invitationDate'];//for date of invitation
				$inviterId=$row['inviterId'];
				$inviterUserName=$row['inviterUserName'];
				$groupname=$row['groupname'];

				//<p>Date of creation: <?php echo $date; </p>
				?>
				<div class="request">
					<div class="group_invite" id="groupInvite<?php echo htmlentities($inviteId); ?>">
						<p class="request-content">You have been invited to join the group <a href="groupspage.php?id=<?php echo htmlentities($groupId); ?>"><?php echo htmlentities($groupname); ?></a> by <a href="userprofile.php?id=<?php echo htmlentities($inviterId); ?>"><?php echo htmlentities($inviterUserName); ?></a> on <?php echo $date; ?>.</p>					
						<p id="acceptOrRejectGroupInvite<?php echo htmlentities($inviteId); ?>"></p>
						<form class="acceptGroupInviteForm" id="acceptGroupInviteForm<?php echo htmlentities($inviteId); ?>" method="post" action="">
							<input type="hidden" name="groupId" value="<?php echo htmlentities($groupId); ?>">	
							<input type="hidden" name="inviteId" value="<?php echo htmlentities($inviteId); ?>">					
							<input type="hidden" name="datetime" value="<?php echo date('Y-m-d H:i:s'); ?>">
							<button type="submit" class="btn accept text-success" value="Accept Invitation"><span class="glyphicon glyphicon-ok"></span></button>
						</form>
						<form class="rejectGroupInviteForm" id="rejectGroupInviteForm<?php echo htmlentities($inviteId); ?>" method="post" action="">
							<input type="hidden" name="groupId" value="<?php echo htmlentities($groupId); ?>">						
							<input type="hidden" name="inviteId" value="<?php echo htmlentities($inviteId); ?>">
							<button type="submit" class="btn reject text-danger" value="Reject Invitation"><span class="glyphicon glyphicon-remove"></span></button>
						</form>

					</div>	
				</div>
				<?php
				
			}

			if($groupInvites==false){
				echo '<p class="error-message" style="display:block;width:300px;margin:0 auto">You have no pending group invites.</p>';
			}

			//echo "Time:  " . number_format(( microtime(true) - $startTime), 10) . " Seconds\n";
			?>

			
		</div>
		<hr>

		<div class="meagles_div">
			<h3 class="meagles-heading">Meagls</h3>
			<div class="allmeagles">
				<?php
				//These groups are are exactly like WhatsApp groups except that here, you send invites to friends to join the group unlike whatsapp where you directly add friends without their consent
				/*$belongToGroups=false;

				$sql="SELECT groupId FROM group_participants_table WHERE participantId='$userId' AND invitationStatus=1";
				$result=mysqli_query($conn,$sql);
				while($row=mysqli_fetch_assoc($result)){
					$belongToGroups=true;

					$groupId=$row['groupId'];

					$sql1="SELECT * FROM groups_table WHERE id='$groupId' ORDER BY lastActivityDateTime DESC";
					$result1=mysqli_query($conn,$sql1); 					
					$row1=mysqli_fetch_assoc($result1);

					$groupname=$row1['groupname'];
					$numberOfParticipants=$row1['numberOfParticipants'];
					$numberOfPendingInvitations=$row1['numberOfPendingInvitations'];
					$datetime=$row1['lastActivityDateTime'];
					$date=date("d-m-Y", strtotime($row1['dateOfCreation']));//to change the format of display date
					?>

					<div class="group" id="<?php echo $groupId; ?>">
						<h3><a href="groupspage.php?id=<?php echo $groupId; ?>"><?php echo $groupname; ?></a></h3>
						<p>Number of participants: <?php echo $numberOfParticipants; ?></p>
						<p>Number of pending Invitations: <?php echo $numberOfPendingInvitations; ?></p>
						<p>Last Activity: <?php echo getTime($datetime); ?></p>
						<p>Date of creation: <?php echo $date; ?></p>
						<hr style='border-top: dotted 5px;'>
					</div>
					<?php
				}

				if($belongToGroups==false){
					echo '<p>You are not a participant of any group!</p>';
				}
				*/
				$counter=0;//counter to keep a track of the array element number..that is, key number
				$sql="SELECT * FROM friends_table WHERE (sender_user_id='$userId' OR receiver_user_id='$userId') AND lastActivityDateTime!='0000-00-00 00:00:00'";
				$result=mysqli_query($conn,$sql);			
								
				while($row=mysqli_fetch_assoc($result)){
					
					$lastActivityDateTime=$row['lastActivityDateTime'];
					$sender_user_id=$row['sender_user_id'];
					$receiver_user_id=$row['receiver_user_id'];
					$friend_request_id=$row['id'];
					if($row['relation']==1){
						
						//so the two users are friends ..now checking which one of them is the current user user whose profile is being accessed and which one is his/her friend
						$friends=true;
						if($sender_user_id==$userId){
							//the current user is the sender therfore, his friend is the receiver
							$sql1="SELECT id,username FROM memberstable WHERE id='$receiver_user_id'";				
							$friend_id=$receiver_user_id;
						}
						else{
							//the current user is the receiver therfore, his friend is the sender
							$sql1="SELECT id,username FROM memberstable WHERE id='$sender_user_id'";
							$friend_id=$sender_user_id;
						}
						$result1=mysqli_query($conn,$sql1);
						$row1=mysqli_fetch_assoc($result1);
						$all_sharing[]=$row1;	

						$all_sharing[$counter]["lastActivityDateTime"]=$lastActivityDateTime;		
						$all_sharing[$counter]["shareType"]="user";//defining the type of this share..that is, since the meme has been shared with this user therefore its a user share
						$counter+=1;
					}
					
				}


				$sql="SELECT groupId FROM group_participants_table WHERE participantId='$userId' AND invitationStatus=1 AND participantStatus!=3";//participantStatus!=3 means this user has not exited the group
				$result=mysqli_query($conn,$sql);
				while($row=mysqli_fetch_assoc($result)){
					$groupId=$row['groupId'];
					$sql1="SELECT id,lastActivityDateTime,groupname FROM groups_table WHERE id='$groupId'";
					$result1=mysqli_query($conn,$sql1);
					$row1=mysqli_fetch_assoc($result1);
					$all_sharing[]=$row1;

					$all_sharing[$counter]["shareType"]="group";//defining the type of this share..that is, since the meme has been shared with this group therefore its a group share
					$counter+=1;
				}				
				if (!empty($all_sharing)) {
					//now sorting according to the "lastActivityDateTime" value...latest activity shares are displayed first
					usort($all_sharing, function($a, $b) {
					    //return $a['lastActivityDateTime'] <=> $b['lastActivityDateTime'];
						if ($a['lastActivityDateTime'] == $b['lastActivityDateTime']) {
					        return 0;
					    }
					    return ($a['lastActivityDateTime'] > $b['lastActivityDateTime']) ? -1 : 1;
					});

					//now printing out the values
					foreach ($all_sharing as $key => $as) {
						if($as['shareType']=="group"){
							//its a group share
							$groupId=mysqli_real_escape_string($conn,$as['id']);
							$lastActivityDateTime=$as['lastActivityDateTime'];
							$groupname=$as['groupname'];

							$sql1010="SELECT groupPicLocation FROM groups_table WHERE id='$groupId'";
							$result1010=mysqli_query($conn,$sql1010);
							$row1010=mysqli_fetch_assoc($result1010);
							$gpL=$row1010['groupPicLocation'];
							?>
								<div class="meagl">
									<a href="groupspage.php?id=<?php echo htmlentities($groupId); ?>"><span class="spanner"></span>
									<img src="../<?php echo $gpL; ?>" class="meagl-dp">
									<p class="meagl-username"><?php echo htmlentities($groupname); ?></p>
									<p class="last-activity">Last Activity: <?php echo getTime($lastActivityDateTime); ?></p>
									</a>
								</div>
							<?php
						}else{
							//its a user share
							$userId=mysqli_real_escape_string($conn,$as['id']);
							$lastActivityDateTime=$as['lastActivityDateTime'];
							$username=$as['username'];

							$sql1010="SELECT profilePictureLocation FROM memberstable WHERE id='$userId'";
							$result1010=mysqli_query($conn,$sql1010);
							$row1010=mysqli_fetch_assoc($result1010);
							$ppL=$row1010['profilePictureLocation'];
							?>
							<div class="meagl">	
								<a href="userimagesharing.php?id=<?php echo htmlentities($userId); ?>"><span class="spanner"></span>
								<img src="../<?php echo $ppL; ?>" class="meagl-dp"><br>						
								<p class="meagl-username"><?php echo htmlentities($username); ?></p>
								<p class="last-activity">Last Activity: <?php echo getTime($lastActivityDateTime); ?></p>	
								</a>						
							</div>
							<?php
						}
					}
				}else{
					echo '<p class="error-message">No meagls yet!</p>';
				}
				?>
			</div>
			<hr>
			<div class="add-height"></div>
		</div>
		
	</div>
</div>
	</div>
	<?php
		/*
	<div id="notifications-tab">
		
		<div class="notifications-column-userprofile notifications-column" id="user-prof-notif">
			<?php
			if(isset($_SESSION['id']))
			{	
				$userId=mysqli_real_escape_string($conn,$_SESSION['id']);
				$sql="SELECT * FROM notifications_table WHERE receiverId='$userId' ORDER BY id DESC";
				$result=mysqli_query($conn,$sql);

				$areThereNotifications=false;
				$notifications_counter=0;
				echo '<h1 id="notif-heading" style="padding-bottom:0px;margin-top:0px;"></h1>';
				echo '<div class="notifications-body">';
				while($row=mysqli_fetch_assoc($result)){
					$areThereNotifications=true;

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

						echo '<a href="'.$nL.'" class="notif-link" id="notification'.$notificationId.'">	
					      <div class="notif">
							<img src="'.$ppL.'" class="notif-dp">
						    <p class="notif-text userprof-notif-text">'.$notification.'</p>							
						  </div>
						  </a>';
					}else{						
						echo '<a href="'.$nL.'" class="notif-link viewed-notification" id="notification'.$notificationId.'">	
					      <div class="notif" style="background-color:#EAEAEA">
							<img src="'.$ppL.'" class="notif-dp">
						    <p class="notif-text userprof-notif-text">'.$notification.'</p>							
						  </div>
						  </a>';
					}
					
				}

				if($areThereNotifications==false){
					echo '<p class="error-message">No notifications yet!</p>';
				}
				else
				{
					?>
					<script>thereAreNotificationsUP(<?php echo $notifications_counter; ?>);</script>
					<?php
				}
				echo "</div>";
			}	
			?>
		</div>
	</div>
	<?php
	}
	else if(isset($_SESSION['id']) && isset($_GET['id']) && $_GET['id']!=$_SESSION['id'])
	{
		*/
	}
	?>
	<div class="small-notifications-column">
	<?php
	if(isset($_SESSION['id']))
	{
		$userId=mysqli_real_escape_string($conn,$_SESSION['id']);
		$sql="SELECT * FROM notifications_table WHERE receiverId='$userId' ORDER BY id DESC";
		$result=mysqli_query($conn,$sql);

		$areThereNotifications=false;
		$notifications_counter=0;
		echo '<div class="small-notifications-body">';
		while($row=mysqli_fetch_assoc($result)){
			$areThereNotifications=true;

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
						<img src="../<?php echo htmlentities($ppL) ?>" class="small-notif-dp">
					    <p class="small-notif-text"><?php echo $notification; ?></p>							
					</div>
				</a>

				<?php
			}
			else
			{
				?>
					
				<a href="<?php echo htmlentities($nL); ?>" class="notif-link" id="notification<?php echo htmlentities($notificationId); ?>">	
				    <div class="notif" style="background-color:#EAEAEA">
						<img src="../<?php echo htmlentities($ppL) ?>" class="small-notif-dp">
					    <p class="small-notif-text"><?php echo $notification; ?></p>							
					</div>
				</a>

				<?php
			}
		}

		if($areThereNotifications==false){
			echo '<p>No notifications yet!</p>';	
			?>
			<script>thereAreNotifications(0);</script>
			<?php		
		}else{
			?>
			<script>thereAreNotifications(<?php echo $notifications_counter; ?>);</script>
			<?php
		}
		echo "</div>";
	}	
	?>
	</div>
	<?php

	
	?>

<?php
//echo "Time:  " . number_format(( microtime(true) - $startTime), 10) . " Seconds\n";
?>
</div>
</body>
</html>