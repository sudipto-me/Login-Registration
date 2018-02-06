<?php 
/***
 * Plugin Name: FrontEnd Login
 * Description: This is a simple elegant frontend login plugin.
 * Author: Sudipto Shakhari
 * Author URI: https://sudipto.me
 * version: 1.0.0 
 */

class WPFrontEndLogin {
    public $errormessage = array();
    public $success = NULL;
    function __construct() {
        add_action('template_redirect',array($this,'wp_frontend_user_login'));
        add_action('template_redirect',array($this,'wp_frontend_user_registration'));
        add_action('wp_enqueue_scripts',array($this,'wp_frontEnd_script'));
        add_shortcode( 'login', array($this,'wp_frontEnd_Login_shortcode'));
        add_shortcode('registration',array($this,'wp_frontEnd_Registration_shortcode'));
        
    }

    function wp_frontEnd_script() {
        wp_enqueue_style('frontend-main', plugins_url( '/css/style.css', __FILE__ ));
    } 

    //Login Process
    //shortcode function for login
    function wp_frontEnd_Login_shortcode($atts,$content = NULL) {  
        if(is_user_logged_in()) {
            $this->success = "You are already logged in.";
            if(isset($this->success)) {?>
                <div class="success">
                    <?php echo $this->success;?>
                </div>
            
            <?php    
            }
            
        } 
        else {
            ob_start();?>
            <div class="login">
                <h2 class="title">Login Form</h2>
                <?php if(!empty($this->errormessage)) {?>
                <div class="error-message">
                    <ol>
                        <?php
                            foreach($this->errormessage as  $message) {?>
                                <li><?php echo  $message;?></li>
                            <?php
                            } 
                        ?>
                    </ol>
                </div>
                <?php
                }
                ?>
                <form action="" method="post" class="frontend-login-form">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="input-lg" placeholder="user name">
                    <label for="password">PassWord:</label>
                    <input type="password" id="password" name="password" class="input-lg" placeholder="password">
                    <?php wp_nonce_field( 'wp_frontend_user_login', 'wp_frontend_login_nonce' ); ?>
                    <input type="submit" name="submit" value="submit" class="btn btn-submit">
                </form>
            </div>
        <?php 
        }

        $html = ob_get_contents();
        ob_clean();
        return $html;
    }

    //login process validation function
    function wp_frontend_user_login() {
        
        //checking nonce value
        if(!isset($_POST['wp_frontend_login_nonce'])) {
            return ;
        }
        if(!wp_verify_nonce( $_POST['wp_frontend_login_nonce'],'wp_frontend_user_login')) {
            return ;
        }
        
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];

        //checking the username and password if empty
        //validation of the username
        $user = get_user_by('login',$username);
        if(!$user){
            $this->errormessage[] = "No user found in this username";
        }

        if($user && ! wp_check_password($password,$user->data->user_pass,$user->ID)) {
            $this->errormessage[] = "Username/Password was wrong";
        }

        if($user && wp_check_password($password,$user->data->user_pass,$user->ID)) {
            wp_redirect(get_home_url());
        }

        if(!empty($username) && !empty($password)) {
            $creds = array();
            $creds['user_login'] = $username;
            $creds['user_password'] = $password;
            $creds['remember'] = true;
            $user = wp_signon( $creds, false );
            if (is_wp_error($user)){
                $this->errormessage[] =  $user->get_error_message();
            } 
        } 
        else {
            $this->errormessage[] = "Required field(s) missing";
        }               
    }

    //Registration Process
    //registration shortcode function
    function wp_frontEnd_Registration_shortcode($atts,$content = NULL) {
        ob_start();?>
        <div class="registration">
            <h2 class="title">Registration Form</h2>
            <?php if(!empty($this->errormessage)) {?>
                <div class="error-message">
                    <ol>
                        <?php
                            foreach($this->errormessage as  $message) {?>
                                <li><?php echo  $message;?></li>
                            <?php
                            } 
                        ?>
                    </ol>
                </div>
            <?php
            }?>
            <?php if(isset($this->success)){?>
                <div class="success">
                    <?php echo $this->success;?>
                </div>
            <?php 
            }
            ?>
            <form action="" method="post" class="frontend-registration-form">
                <label for="username">Username *</label>
                <input type="text" name="username" id="username" class="input-lg" placeholder="user name *">

                <label for="firstname">First Name</label>
                <input type="text" name="firstname" id="firstname"  class="input-lg" placeholder="First Name">

                <label for="lastname">Last name</label>
                <input type="text" name="lastname" id="lastname" class="input-lg" placeholder="Last Name">

                <label for="email">Email *</label>
                <input type="email" name="email" id="email"  class="input-lg" placeholder="email *">

                <label for="password1">Enter Password *</label>
                <input type="password" name="password1" id="password1" class="input-lg" placeholder="enter password *">

                <label for="password2">Enter Password Again *</label>
                <input type="password" name="password2" id="password2" class="input-lg" placeholder="enter password again *">

                <?php wp_nonce_field( 'wp_frontend_user_registration', 'wp_frontend_registration_nonce' ); ?>
                <input type="submit" name="register" value="register" class="btn btn-submit">
            </form>
        </div><!--.registration-->
        <?php
        $html = ob_get_contents();
        ob_clean();
        return $html; 
    }

    //registration validation function
    function wp_frontend_user_registration() {
        
        //validating nonce
        if(!isset($_POST['wp_frontend_registration_nonce'])) {
            return ;
        }
        if(!wp_verify_nonce( $_POST['wp_frontend_registration_nonce'],'wp_frontend_user_registration')) {
            return ;
        }

        $username = sanitize_text_field(trim($_POST['username']));
        $firstname = sanitize_text_field(trim($_POST['firstname']));
        $lastname = sanitize_text_field(trim($_POST['lastname']));
        $email = sanitize_text_field(trim($_POST['email']));
        $password1 = $_POST['password1'];
        $password2 = $_POST['password2'];

        //checking the password is correct
        if($password1 != $password2) {
            $this->errormessage[] = "Password mitchmatch";
            
            
        }
        //check if the email exists
        if(email_exists($email)) {
            $this->errormessage[] = "Email Exists.";
            
        }
        //check the valid email address
        if(is_email($email) === false) {
            $this->errormessage[] = "Invalid Email.";
        } 
        //check if the username is already exists
        if(username_exists($username)) {
            $this->errormessage[] = "Username Exists";
        }
        

        //checking the fields are not empty
        if(!empty($username)  && !empty($email) && !empty($password1) && !empty($password2)) {
            $userdata = array(
                'user_login' => $username,
                'user_pass' => $password1,
                'user_email'=> $email,
                'first_name'=> $firstname,
                'last_name'=> $lastname
            );

            $user_id = wp_insert_user($userdata);
            //On success
            if(!is_wp_error($user_id)) {
                $this->success = "Congrats! You have successfully registered with us.";
            }
        }
        else {
            $this->errormessage[] = "Required Field(s) Missing";
            
        }
    }
}

new WPFrontEndLogin();

?>