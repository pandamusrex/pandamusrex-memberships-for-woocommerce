<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships_Membership_Form {
    public static function render_membership_form_for_my_account() {

        $form_elements = [
            [
                'type' => 'heading',
                'text' => 'Hey everyone, welcome to the Club!'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'We\'ve finally got our 501(c)7!',
                    'That means we can pool our money for events without owing taxes on the money we collect.',
                    'Unfortunately, the IRS has a lot of rules about this: basically, we can only pool money from Club members.',
                    'Because of that, we need need three things from everyone who wants to attend events:',
                    '1. You\'ll need to declare yourself a member of the Social Group.',
                    '2. You\'ll need to pay $1 for an annual membership.',
                    '3. We need an email address for the Club ***that you actually monitor***',
                    'Just to be clear: weekly MeetUps, individually paid restaurant trips, and every other event are still free and open to anyone and everyone. Membership requirements *only apply to tax-free merchandise and events where we pool our money*.',
                    'Sorry. it\'s the IRS.',
                    'The upside is we get great deals on group packages, venues, and events like Trick or Meat. No cramming into the bar at Fogo like sardines!',
                    'Didja read that?'
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I Understand',
                'user_meta_key' => 'tat_accepts_terms'
            ],
            [
                'type' => 'heading',
                'text' => 'Membership Qualification'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'This section covers the IRS-required legalese of joining the Social Group.',
                    'The full Bylaws can be read here.',
                    '**Non-Discrimination**',
                    'Membership is open to any person at least eighteen (18) years-old without regard to race, religious creed, skin color, national origin, ancestry, physical disability, mental disability, medical condition, marital status, gender, or sexual orientation of such persons.',
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I am at least 18 years of age',
                'user_meta_key' => 'tat_at_least_18'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Declaration of Membership',
                    'In order to qualify for membership in the Club, a member shall be required to declare themselves to be a furry.',
                    'i. Definition of a Furry',
                    'A furry, according to Merriam-Webster is a person who identifies with and enjoys sometimes dressing as anthropomorphic animals or creatures especially as a member of a fandom devoted to the practice.',
                    'ii. Expanded definition of a Furry',
                    'The Club explicitly adopts a more expansive definition of furry. A furry is anyone with an interest in furry themes, furry attire, furry personas, anthropomorphism, pup and/or handler play, or non-furry anthropomorphic personas including but not limited to plane-sonas, mechano-sonas (such as Protogens), scalies, and other commonly accepted sub-groups of the furry fandom.',
                    'I declare I\'m a furry as defined by either or both of the above definitions.'
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I\'m a furry',
                'user_meta_key' => 'tat_is_a_furry'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Exclusionary Clauses',
                    'Members of the following groups must have their applications reviewed before approval. Please Contact Us through one of the methods listed for more information.',
                    'Hate Groups'
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I am not a member of an SPLC designated hate group.',
                'user_meta_key' => 'tat_no_hate_group'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Sexual Criminals'
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I have never been convicted of a sexual crime.',
                'user_meta_key' => 'tat_no_sex_crime'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Felons'
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I have never been convicted of a felony.',
                'user_meta_key' => 'tat_no_felony'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Convention & Group Bans'
                ]
            ],
            [
                'type' => 'checkbox',
                'required' => TRUE,
                'text' => 'I am not currently banned from any conventions or gatherings (furry or otherwise).',
                'user_meta_key' => 'tat_no_bans'
            ],
            [
                'type' => 'heading',
                'text' => 'Membership Information'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Next up, we\'re going to gather your info for our database.',
                    'We will never sell this data, nor make it public. We are a private social group.',
                ]
            ],
            [
                'type' => 'text',
                'label' => 'Legal First Name',
                'required' => TRUE,
                'user_meta_key' => 'tat_legal_first_name'
            ],
            [
                'type' => 'text',
                'label' => 'Legal Last Name',
                'required' => TRUE,
                'user_meta_key' => 'tat_legal_last_name'
            ],
            [
                'type' => 'text',
                'label' => 'Fursona Name',
                'required' => TRUE,
                'user_meta_key' => 'tat_fursona_name'
            ],
            [
                'type' => 'radiobutton',
                'label' => 'Would you prefer your Fursona Name to be used in Club e-mails?',
                'values' => [
                    'Yes',
                    'No'
                ],
                'user_meta_key' => 'tat_use_fursona_name_in_club_emails'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Email',
                    'This is super important.',
                    'This email address will be used to send you legally mandated notifications like upcoming votes on vacant Board seats or other issues that we\'re required, by law, to inform you of.',
                    'Please make sure this email address is monitored.',
                    'You may want to allow-list the TiesAndTails.Club  domain and the alternate email: TiesAndTailsClub@gmail.com',
                ]
            ],            
            [
                'type' => 'email',
                'label' => 'Monitored Email',
                'required' => TRUE,
                'user_meta_key' => 'tat_monitored_email'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'What other kinds of emails is it ok to send you at this address?'
                ]
            ],
            [
                'type' => 'checkbox',
                'text' => 'Restaurant Outings',
                'user_meta_key' => 'tat_outings_emails_ok'
            ],
            [
                'type' => 'checkbox',
                'text' => 'Seasonal Events',
                'user_meta_key' => 'tat_event_emails_ok'
            ],
            [
                'type' => 'checkbox',
                'text' => 'Club Newsletter',
                'user_meta_key' => 'tat_newsletter_emails_ok'
            ],
            [
                'type' => 'checkbox',
                'text' => 'New - Club Shop Listings',
                'user_meta_key' => 'tat_shop_emails_ok'
            ],
            [
                'type' => 'bodytext',
                'text' =>  [
                    'Once you hit Submit you\'re all done!',
                ]
            ]
        ];

        // TODO CHECK FOR FORM SUBMITTAL REQUEST AND PROCESS IT

        echo "<form>";

        foreach ($form_elements as $element) {
            if (! is_array( $element)) {
                echo "<p>Error: Form element must be an array.</p>";
            } elseif (! array_key_exists( 'type', $element )) {
                echo "<p>Error: Form element array must contain type key.</p>";
            } else {
                $type = $element['type'];
                if ($type == 'heading') {
                    $text = $element['text'];
                    echo "<h2>";
                    echo esc_html($text);
                    echo "</h2>";
                    continue;
                }

                if ($type == 'bodytext') {
                    $text = $element['text'];
                    if (! is_array( $text )) {
                        echo "<p>";
                        echo esc_html($text);
                        echo "</p>";
                    } else {
                        foreach ($text as $line) {
                            echo "<p>";
                            echo esc_html($line);
                            echo "</p>";
                        }
                    }
                    continue;
                }

                if ($type == 'checkbox') {
                    $text = $element['text'];
                    $key = $element['user_meta_key'];
                    $required = array_key_exists('required', $element);
                    $checked = empty( get_user_meta( get_current_user_id(), $key, TRUE ) ) ? 'CHECKED' : '';
                    echo "<p>";
                    echo "<input type='checkbox' id='" . esc_attr( $key ) . "' value='". esc_attr($text) ."' " . esc_attr($checked) . ">";
                    echo "<label for='" . esc_attr( $key ) . "'>" . esc_html($text) . "</label>";
                    if ( $required ) {
                        echo "<font color='red'>*</font>";
                    }
                    echo "</p>";
                    continue;
                }

                if ($type == 'text') {
                    $label = $element['label'];
                    $key = $element['user_meta_key'];
                    $required = array_key_exists('required', $element);
                    $initial_value = get_user_meta( get_current_user_id(), $key, TRUE );
                    echo "<p>";
                    echo "<label for='" . esc_attr( $key ) . "'>" . esc_html($label) . "</label>";
                    if ( $required ) {
                        echo "<font color='red'>*</font>";
                    }
                    echo "<br/>";
                    echo "<input type='text' id='" . esc_attr( $key ) . "' name='" . esc_attr( $key ) . "'value='". esc_attr($initial_value) ."'>";
                    echo "</p>";
                    continue;
                }

                if ($type == 'email') {
                    $label = $element['label'];
                    $key = $element['user_meta_key'];
                    $required = array_key_exists('required', $element);
                    $initial_value = get_user_meta( get_current_user_id(), $key, TRUE );
                    echo "<p>";
                    echo "<label for='" . esc_attr( $key ) . "'>" . esc_html($label) . "</label>";
                    if ( $required ) {
                        echo "<font color='red'>*</font>";
                    }
                    echo "<br/>";
                    echo "<input type='text' id='" . esc_attr( $key ) . "' name='" . esc_attr( $key ) . "'value='". esc_attr($initial_value) ."'>";
                    echo "</p>";
                    continue;
                }
            }
        }

        echo "<input type='submit' id='pandamusrex_memberships_membership_form_submit' value='Submit'>";

        echo "</form>";
    }
}
