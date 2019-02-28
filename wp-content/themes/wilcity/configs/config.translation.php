<?php
return array(
	'online' => 'Online',
	'offline' => 'Offline',
	'deleteMessage' => 'Do you want to delete this message?',
	'startConversation' => 'Starting a conversation with an author now.',
	'searchAuthor' => 'Search Author',
	'postType' => 'Post Type',
	'status'   => 'Status',
	'connectWithSocialNetworks' => 'Or Connect With',
	'fbLoginWarning' => 'Facebook Init is not loaded. Check that you are not running any blocking software or that you have tracking protection turned off if you use Firefox',
	'createdAccountSuccessfully' => 'Congrats! Your account has been created successfully.',
	'couldNotCreateAccount' => 'ERROR: We could not create your account. Please try later.',
	'usernameExists' => 'ERROR: Sorry, The username is not available. Please with another username.',
	'emailExists' => 'ERROR: An account with this email already exists on the website.',
	'invalidEmail' => 'ERROR: Invalid Email',
	'needCompleteAllRequiredFields' => 'ERROR: Please complete all required fields.',
	'needAgreeToTerm' => 'ERROR: Sorry, To create an account on our site, you have to agree to our terms conditionals.',
	'needAgreeToPolicy' => 'ERROR: Sorry, To create an account on our site, you have to agree to our privacy policy.',
	'disabledLogin' => 'Sorry, We are disabled this service temporary.',
	'youAreLoggedInAlready' => 'You are logged in already.',
	'deletedNotification' => 'Congrats! The notification has been deleted successfully.',
	'couldNotDeleteNotification' => 'Oops! We could not delete this notification. Please recheck your Notification ID.',
	'confirmDeleteNotification' => 'Do you want to delete this notification?',
	'idIsRequired' => 'The ID is required',
	'noDataFound' => 'No Data Found',
	'couldNotSendMessage' => 'OOps! We could not send this message, please try clicking on Send again.',
	'messageContentIsRequired' => 'Message Content is required',
	'chatFriendIDIsRequired' => 'Chat friend id is required',
	'authorMessageHasBeenDelete' => 'Congrats! The author messages has been deleted successfully.',
	'messageHasBeenDelete' => 'Congrats! The message has been deleted successfully.',
	'weCouldNotDeleteMessage' => 'Oops! Something went wrong, We could not delete this message.',
	'weCouldNotDeleteAuthorMessage' => 'Oops! Something went wrong, We could not delete the author messages.',
	'authorIDIsRequired' => 'Author ID is required',
	'msgIDIsRequired' => 'Message ID is required',
	'fetchedAllMessages' => 'Fetched all messages',
	'aFewSecondAgo' => 'A few seconds ago',
	'xMinutesAgo' => '%s minutes ago',
	'xHoursAgo' => '%s hours ago',
	'noMessage' => 'There are no messages',
	'allNotificationsIsLoaded' => 'All Notification is loaded',
	'passwordHasBeenUpdated' => 'Congrats! Your password has been updated successfully.',
	'all' => 'All',
	'confirmDeleteFavorites' => 'Do you want delete it from your favorites',
	'backTo' => 'Back To',
	'logoutDesc' => 'Do you want to logout?',
	'requiredLogin' => 'You need to login to your account first',
	'continue'  => 'Continue',
	'logout' => 'Logout',
	'favorite' => 'Favorite',
	'report' => 'Report',
	'editProfile' => 'Edit Profile',
	'foundNoUser' => 'User profile does not exist.',
	'userIDIsRequired' => 'Take a Photo',
	'takeAPhoto' => 'Take a Photo',
	'uploadPhoto' => 'Upload photo',
	'uploadPhotoFromLibrary' => 'Upload photo from Library',
	'firstName' => 'First Name',
	'lastName' => 'Last Name',
	'displayName' => 'Display Name',
	'avatar' => 'Avatar',
	'coverImg' => 'Cover Image',
	'email' => 'Email',
	'position' => 'Position',
	'introYourSelf' => 'Intro yourself',
	'address' => 'Address',
	'phone' => 'Phone',
	'website' => 'Website',
	'socialNetworks' => 'Social Networks',
	'changePassword' => 'Change Password',
	'currentPassword' => 'Current Password',
	'newPassword' => 'New Password',
	'confirmNewPassword' => 'Confirm Password',
	'youNeedToCompleteAllRequired' => 'Please complete all required fields',
	'validationData' => array(
		'email' => array(
			'presence' => array(
				'message' => 'Email is required'
			),
			'special' => array(
				'message' => 'Invalid email address'
			)
		),
		'phone' => array(
			'presence' => array(
				'message' => 'Phone number is required'
			),
			'special' => array(
				'message' => 'Invalid phone number'
			)
		),
		'url' => array(
			'presence' => array(
				'message' => 'URL is required'
			),
			'special' => array(
				'message' => 'Invalid URL'
			)
		),
		'username' => array(
			'presence' => array(
				'message' => 'Username is required'
			),
			'length' => array(
				'minimum'=> 3,
				'message' => 'Your username must be at least 3 characters'
			)
		),
		'password' => array(
			'presence' => array(
				'message' => 'Please enter a password'
			),
			'length' => array(
				'minimum' => 5,
				'message' => 'Your password must be at least 5 characters'
			)
		),
		'confirmPassword' => array(
			'presence' => array(
				'message' => 'Password does not match the confirm password'
			)
		),
		'firstName' => array(
			'presence' => array(
				'message' => 'Please enter a password'
			),
			'length' => array(
				'message' => 'Your first name must be at least %s characters'
			)
		),
		'lastName' => array(
			'presence' => array(
				'message' => 'Please enter your last name'
			),
			'length' => array(
				'minimum' => 3,
				'message' => 'Your last name must be at least 3 characters'
			)
		),
		'displayName' => array(
			'presence' => array(
				'message' => 'Please enter your display name'
			),
			'length' => array(
				'minimum' => 3,
				'message' => 'Your display name must be at least 3 characters'
			)
		),
		'agreeToTerms' => array(
			'presence' => array(
				'message' => 'You must agree to our terms conditionals.'
			),
		),
		'agreeToPolicy' => array(
			'presence' => array(
				'message' => 'You must agree to our policy privacy.'
			),
		)
	),
	'invalidGooglereCaptcha'=>'Pleas verify reCaptcha first.',
	'profileHasBeenUpdated'=>'Congrats! Your profile have been updated',
	'errorUpdateProfile' => 'ERROR: Something went wrong, We could not update your profile.',
	'errorUpdatePassword' => 'ERROR: The password confirmation must be matched the new password.',
	'sendAnEmailIfIReceiveAMessageFromAdmin' => 'Send an email if I receive a message from admin',
	'reportMsg' => 'Thank for your reporting!',
	'weNeedYourReportMsg' => 'Please give us your reason',
	'aPostStatus' => array(
		array(
			'status' => 'Published',
			'icon'   => 'la la-share-alt',
			'bgColor' => 'bg-gradient-1',
			'post_status' => 'publish',
			'total'  => 0
		),
		array(
			'status' => 'In Review',
			'icon'   => 'la la-refresh',
			'bgColor' => 'bg-gradient-2',
			'post_status' => 'pending',
			'total'  => 0
		),
		array(
			'status' => 'Unpaid',
			'icon'   => 'la la-money',
			'bgColor' => 'bg-gradient-3',
			'post_status' => 'unpaid',
			'total'  => 0
		),
		array(
			'status' => 'Expired',
			'icon'   => 'la la-exclamation-triangle',
			'bgColor' => 'bg-gradient-4',
			'post_status' => 'expired',
			'total'  => 0
		)
	),
	'aEventStatus' => array(
		array(
			'status' => 'Upcoming',
			'icon'   => 'la la-calendar-o',
			'bgColor' => 'bg-gradient-1',
			'post_status' => 'publish',
			'total'  => 0
		),
		array(
			'status' => 'OnGoing',
			'icon'   => 'la la-calendar-o',
			'bgColor' => 'bg-gradient-2',
			'post_status' => 'ongoing',
			'total'  => 0
		),
		array(
			'status' => 'Expired',
			'icon'   => 'la la-calendar-o',
			'bgColor' => 'bg-gradient-3',
			'post_status' => 'expired',
			'total'  => 0
		),
		array(
			'status' => 'In Review',
			'icon'   => 'la la-calendar-o',
			'bgColor' => 'bg-gradient-4',
			'post_status' => 'pending',
			'total'  => 0
		),
		array(
			'status' => 'Temporary Close',
			'icon'   => 'la la-calendar-o',
			'bgColor' => 'bg-gradient-4',
			'post_status' => 'temporary_close',
			'total'  => 0
		)
	),
	'gotAllFavorites'  => 'All favorites have been displayed',
	'noFavorites'  => 'There are no favorites',
	'tokenExpired' => 'The token has been expired, please log into the app to generate a new token',
	'profile' => 'Profile',
	'messages' => 'Messages',
	'favorites' => 'Favorites',
	'doNotHaveAnyArticleYet' => 'You do not have any article yet.',
	403 => 'You do not have permission to access this page',
	'invalidUserNameOrPassword' => 'Invalid User Name Or Password',
	'download' => 'Download',
	'geoCodeIsNotSupportedByBrowser' => 'Geolocation is not supported by this browser.',
	'deleteMessage' => 'Delete Message',
	'askForAllowAccessingLocationTitle' => esc_html__('Allow accessing your location while you are using the app?','wilcity'),
	'askForAllowAccessingLocationDesc' => esc_html__('Your current location will be used for nearby search results.','wilcity'),
	'seeMoreDiscussions' => esc_html__('See more discussions','wilcity'),
	'allRegions' => esc_html__('All Regions','wilcity'),
	'nearby' => esc_html__('Nearby','wilcity'),
	'networkError' => esc_html__('Network Error','wilcity'),
	'retry' => esc_html__('Tap To Retry','wilcity'),
	'weAreWorking' => esc_html__('We are working on this feature','wilcity'),
	'searchResults' => esc_html__('Search Results','wilcity'),
	'noPostFound' => esc_html__('No Posts Found','wilcity'),
	'discussion' => esc_html__('Discussion','wilcity'),
	'discussions' => esc_html__('Discussions','wilcity'),
	'hostedBy' => esc_html__('Hosted By','wilcity'),
	'editReview' => esc_html__('Edit review','wilcity'),
	'always_open' => esc_html__('Open 24/7','wilcity'),
	'whatAreULookingFor' => esc_html__('What are you looking for?','wilcity'),
	'notFound' => esc_html__('Not Found','wilcity'),
	'reviewIDIsRequired' => esc_html__('The review id is required.','wilcity'),
	'viewProfile' => esc_html__('View Profile','wilcity'),
	'inReviews' => esc_html__('In Reviews','wilcity'),
	'addSocial' => esc_html__('Add Social','wilcity'),
	'searchNow' => esc_html__('Search Now','wilcity'),
	'temporaryClose' => esc_html__('Temporary Close','wilcity'),
	'back' => esc_html__('Back','wilcity'),
	'expiryOn' => esc_html__('Expiry On','wilcity'),
	'views' => esc_html__('Views','wilcity'),
	'shares' => esc_html__('Shares','wilcity'),
	'home' => esc_html__('Home','wilcity'),
	'searchAsIMoveTheMap' => esc_html__('Search as I move the map','wilcity'),
	'allListings' => esc_html__('All Listings','wilcity'),
	'allCategories' => esc_html__('All Categories','wilcity'),
	'allLocations' => esc_html__('All Locations','wilcity'),
	'allTags' => esc_html__('All Tags','wilcity'),
	'geolocationError' => esc_html__('Geolocation is not supported by this browser.','wilcity'),
	'promotions' => esc_html__('Promotions','wilcity'),
	'askChangePlan' => esc_html__('Just a friendly popup to ensure that you want to change your subscription level?','wilcity'),
	'changePlan' => esc_html__('Change Plan','wilcity'),
	'active' => esc_html__('Active','wilcity'),
	'getNow' => esc_html__('Get Now','wilcity'),
	'listingType' => esc_html__('Listing Type','wilcity'),
	'currency' => esc_html__('Currency','wilcity'),
	'billingType' => esc_html__('Billing Type','wilcity'),
	'nextBillingDate' => esc_html__('Next Billing Date','wilcity'),
	'currentPlan' => esc_html__('Current Plan','wilcity'),
	'remainingItems' => esc_html__('Remaining Items','wilcity'),
	'billingHistory' => esc_html__('Billing History','wilcity'),
	'subTotal' => esc_html__('Sub Total','wilcity'),
	'discount' => esc_html__('Discount','wilcity'),
	'planName' => esc_html__('Plan Name','wilcity'),
	'planType' => esc_html__('Plan Type','wilcity'),
	'gateway' => esc_html__('Gateway','wilcity'),
	'payfor' => esc_html__('Invoice Pay For','wilcity'),
	'date' => esc_html__('Date','wilcity'),
	'description' => esc_html__('Description','wilcity'),
	'viewDetails' => esc_html__('View Details','wilcity'),
	'viewAll' => esc_html__('View All','wilcity'),
	'amount' => esc_html__('Amount','wilcity'),
	'more' => esc_html__('More','wilcity'),
	'categories' => esc_html__('Categories','wilcity'),
	'saveChanges' => esc_html__('Save Changes','wilcity'),
	'basicInfo' => esc_html__('Basic Info','wilcity'),
	'followAndContact' => esc_html__('Follow & Contact','wilcity'),
	'resetPassword' => esc_html__('Reset Password','wilcity'),
	'ihaveanaccount' => esc_html__('Already have an account?','wilcity'),
	'username' => esc_html__('Username','wilcity'),
	'usernameOrEmail' => esc_html__('Username or Email Address','wilcity'),
	'password' => esc_html__('Password','wilcity'),
	'lostPassword' => esc_html__('Lost your password?','wilcity'),
	'donthaveanaccount' => esc_html__('Don\'t have an account?','wilcity'),
	'rememberMe' => esc_html__('Remember me?','wilcity'),
	'login' => esc_html__('Login','wilcity'),
	'register' => esc_html__('Register','wilcity'),
	'isShowOnHome' =>  esc_html__('Do you want to show the section content on the Home tab?','wilcity'),
	'viewMoreComments' =>  esc_html__('View more comments','wilcity'),
	'reportTitle' =>  esc_html__('Report abuse','wilcity'),
	'pinToTopOfReview' =>  esc_html__('Pin to Top of Review','wilcity'),
	'unPinToTopOfReview' =>  esc_html__('Unpin to Top of Review','wilcity'),
	'seeMoreReview' => esc_html__('See More Reviews','wilcity'),
	'eventName' => esc_html__('Event Name','wilcity'),
	'peopleInterested' => esc_html__('People interested','wilcity'),
	'upcoming' => esc_html__('Upcoming','wilcity'),
	'ongoing' => esc_html__('Ongoing','wilcity'),
	'expired' => esc_html__('Expired','wilcity'),
	'promote' => esc_html__('Promote','wilcity'),
	'title' => esc_html__('Title','wilcity'),
	'type' => esc_html__('Type','wilcity'),
	'oChart' => array(
		'oHeading' => array(
			'views'     => esc_html__('Total Views','wilcity'),
			'favorites' => esc_html__('Total Favorites','wilcity'),
			'shares'    => esc_html__('Total Shares','wilcity'),
			'ratings'   => esc_html__('Average Rating','wilcity')
		),
		'oLabels' => array(
			'views' => esc_html__('Views','wilcity'),
			'favorites' => esc_html__('Favorites','wilcity'),
			'shares'    => esc_html__('Shares','wilcity'),
			'ratings'   => esc_html__('Ratings','wilcity')
		),
		'up' => esc_html__('Up','wilcity'),
		'down' => esc_html__('Down','wilcity')
	),
	'noOlderNotifications'    => esc_html__('No older notifications.','wilcity'),
	'notifications'    => esc_html__('Notifications','wilcity'),
	'seeAll'    => esc_html__('See All','wilcity'),
	'of'    => esc_html__('of','wilcity'),
	'gallery'    => esc_html__('Gallery','wilcity'),
	'favoriteTooltip'    => esc_html__('Save to my favorites','wilcity'),
	'oneResult'    => esc_html__('Result','wilcity'),
	'twoResults'    => esc_html__('Results','wilcity'),
	'moreThanTwoResults' => esc_html__('Results','wilcity'),
	'filterByTags'    => esc_html__('Filter By Tags','wilcity'),
	'to'    => esc_html__('To','wilcity'),
	'send'    => esc_html__('Send','wilcity'),
	'message'    => esc_html__('Message','wilcity'),
	'newMessage'    => esc_html__('New Message','wilcity'),
	'searchUsersInChat'    => esc_html__('Search by username','wilcity'),
	'recipientInfo'    => esc_html__('Recipient information','wilcity'),
	'inbox'    => esc_html__('Inbox','wilcity'),
	'deleteMsg'    => esc_html__('Delete Message','wilcity'),
	'search'    => esc_html__('Search','wilcity'),
	'couldNotAddProduct' => esc_html__('We could not add product to cart','wilcity'),
	'directBankTransfer' => esc_html__('Direct Bank Transfer','wilcity'),
	'totalLabel' => esc_html__('Total','wilcity'),
	'boostSaleBtn' => esc_html__('Boost now','wilcity'),
	'selectAdsPosition' => esc_html__('Select Ads Positions','wilcity'),
	'selectAdsDesc' => esc_html__('Boost this post to a specify positions on the website','wilcity'),
	'boostPost' => esc_html__('Boost Post','wilcity'),
	'boostEvent' => esc_html__('Boost Event','wilcity'),
	'selectPlans' => esc_html__('Select Plans','wilcity'),
	'name' => esc_html__('Name','wilcity'),
	'addVideoBtn' => esc_html__('Add Video','wilcity'),
	'videoLinkPlaceholder' => esc_html__('Video Link','wilcity'),
	'image' => esc_html__('Image','wilcity'),
	'images' => esc_html__('Images','wilcity'),
	'uploadVideosTitle' => esc_html__('Upload Videos','wilcity'),
	'uploadVideosDesc' => esc_html__('Add more videos to this listing','wilcity'),
	'uploadSingleImageTitle' => esc_html__('Upload Image','wilcity'),
	'uploadMultipleImagesTitle' => esc_html__('Upload Images','wilcity'),
	'uploadMultipleImagesDesc' => esc_html__('Add more images to this listing','wilcity'),
	'maximumVideosWarning'=> esc_html__('You can add a maximum of %s videos','wilcity'),
	'maximumImgsWarning'=> esc_html__('You can upload a maximum of %s images','wilcity'),
	'weNeedYour'=> esc_html__('We need your','wilcity'),
	'showMap'   => esc_html__('Show map','wilcity'),
	'enterAQuery'=> esc_html__('Enter a query','wilcity'),
	'day'       => esc_html__('Day','wilcity'),
	'starts'    => esc_html__('Starts','wilcity'),
	'endsOn'    => esc_html__('End','wilcity'),
	'time'      => esc_html__('Time','wilcity'),
	'photo'     => esc_html__('Photo','wilcity'),
	'photos'    => esc_html__('Photos','wilcity'),
	'noComments'   => esc_html__('No Comment','wilcity'),
	'comment'   => esc_html__('Comment','wilcity'),
	'comments'  => esc_html__('Comments','wilcity'),
	'share'     => esc_html__('Share','wilcity'),
	'like'      => esc_html__('Like','wilcity'),
	'likes'     => esc_html__('Likes','wilcity'),
	'liked'     => esc_html__('Liked','wilcity'),
	'typeAMessage' => esc_html__('Type a message...','wilcity'),
	'confirmHide'      => esc_html__('Are you sure that you want to hide this listing?','wilcity'),
	'confirmRePublish'      => esc_html__('Do you want to re-publish this listing?','wilcity'),
	'wrongConfirmation'     => esc_html__('ERROR: Wrong the confirmation code.','wilcity'),
	'confirmDelete' => esc_html__('Press x to confirm that you want to delete this listing','wilcity'),
	'remove'      => esc_html__('Remove','wilcity'),
	'hide'      => esc_html__('Hide','wilcity'),
	'edit'      => esc_html__('Edit','wilcity'),
	'delete'    => esc_html__('Delete','wilcity'),
	'ok'        => esc_html__('Oke','wilcity'),
	'publish'   => esc_html__('Publish','wilcity'),
	'submit'    => esc_html__('Submit','wilcity'),
	'cancel'    => esc_html__('Cancel','wilcity'),
	'confirmDeleteComment' => esc_html__('Are you want to delete this comment?','wilcity'),
	'reportReview' => esc_html__('Report review','wilcity'),
	'reviewFor' => esc_html__('Review For','wilcity'),
	'reviewsFor' => esc_html__('Reviews For','wilcity'),
	'addReview' => esc_html__('Add a review','wilcity'),
	'averageRating' => esc_html__('Average Rating','wilcity'),
	'basicInfoEvent' => esc_html__('Basic Info','wilcity'),
	'basicInfoEventDesc' => esc_html__('This info will also appear in Listing and any ads created for this event','wilcity'),
	'eventVideo' => esc_html__('Event Video','wilcity'),
	'addPhotoVideoPopupTitle' => esc_html__('Add Photos And Videos','wilcity'),
	'location' => esc_html__('Location','wilcity'),
	'aEventFrequency' => array(
		array(
			'name'  => esc_html__('Occurs once','wilcity'),
			'value' => 'occurs_once'
		),
		array(
			'name'  => esc_html__('Daily','wilcity'),
			'value' => 'daily'
		),
		array(
			'name'  => esc_html__('Weekly','wilcity'),
			'value' => 'weekly'
		)
	),
	'frequency'     => esc_html__('Frequency','wilcity'),
	'details'       => esc_html__('Details','wilcity'),
	'detailsDesc'   => esc_html__('Let people know what type of event you\'re hosting and what to expect','wilcity'),
	'oDaysOfWeek'   => array(
		array(
			'value' => 'sunday',
			'label' => esc_html__('Sun','wilcity')
		),
		array(
			'value' => 'monday',
			'label' => esc_html__('Mon','wilcity')
		),
		array(
			'value' => 'tuesday',
			'label' => esc_html__('Tue','wilcity')
		),
		array(
			'value' => 'wednesday',
			'label' => esc_html__('Wed','wilcity')
		),
		array(
			'value' => 'thursday',
			'label' => esc_html__('Thurs','wilcity')
		),
		array(
			'value' => 'friday',
			'label' => esc_html__('Fri','wilcity')
		),
		array(
			'value' => 'saturday',
			'label' => esc_html__('Sat','wilcity')
		)
	),
	'claimListing'  => esc_html__('Claim This Listing','wilcity'),
	'noClaimFields' => esc_html__('There are no fields. Please go to Wiloke Listing Tools -> Claim Listing to add some fields','wilcity'),
	'pressToCopy' => esc_html__('Press Ctrl+C to copy this link','wilcity'),
	'copyLink'        => esc_html__('Copy Link','wilcity'),
	'wantToDeleteDiscussion'    => esc_html__('Are you sure want to delete this comment?','wilcity'),
	'passwordNotMatched' => esc_html__('The confirm password must be matched the new password', 'wilcity'),
	'reset' => esc_html__('Reset','wilcity')
);