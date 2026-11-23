<?php
// English – base language. Every key must exist here.
// Keys "ability.<code>" and "skill.<code>" use the codes from the tables Abilities and Skills.
return [
    // General
    'language'            => 'Language',
    'add'                 => 'Add',

    // Character sheet: page and bar
    'sandbox.title'       => 'Character sheet',
    'character_summary'   => 'Character summary',
    'level'               => 'Level',
    'hit_points'          => 'Hit points',
    'lose_hp'             => 'Lose 1 hit point',
    'gain_hp'             => 'Gain 1 hit point',
    'armor_class'         => 'Armor class',
    'initiative'          => 'Initiative',
    'speed'               => 'Speed',
    'inspiration'         => 'Inspiration',

    // Basics
    'basics'              => 'Basics',
    'character_name'      => 'Character name',
    'species'             => 'Species',
    'class'               => 'Class',
    'subclass'            => 'Subclass',
    'background'          => 'Background',

    // Combat values
    'combat_values'       => 'Combat values',
    'walking_speed'       => 'Walking speed',
    'proficiency_bonus'   => 'Proficiency bonus',

    // Hit points and armor
    'hp_current'          => 'Current',
    'hp_maximum'          => 'Maximum',
    'hp_temporary'        => 'Temporary',
    'hit_dice'            => 'Hit dice',
    'used'                => 'Used',
    'armor'               => 'Armor',
    'shield'              => 'Shield',
    'light_armor'         => 'Light armor',
    'medium_armor'        => 'Medium armor',
    'heavy_armor'         => 'Heavy armor',

    // Defense and conditions
    'defense'             => 'Defense',
    'add_defense_line'    => 'Add a defense line',
    'influence'           => 'Influence',
    'damage_type'         => 'Damage type',
    'remove_line'         => 'Remove this line',
    'conditions'          => 'Conditions',
    'condition'           => 'Condition',
    'add_condition'       => 'Add a condition',
    'remove_condition'    => 'Remove this condition',

    // Abilities
    'abilities'           => 'Abilities',
    'saving_throw'        => 'Saving throw',
    'score'               => 'Score',
    'ability.strength'     => 'Strength',
    'ability.dexterity'    => 'Dexterity',
    'ability.constitution' => 'Constitution',
    'ability.intelligence' => 'Intelligence',
    'ability.wisdom'       => 'Wisdom',
    'ability.charisma'     => 'Charisma',
    'skill.athletics'       => 'Athletics',
    'skill.acrobatics'      => 'Acrobatics',
    'skill.sleight_of_hand'  => 'Sleight of Hand',
    'skill.stealth'         => 'Stealth',
    'skill.arcana'          => 'Arcana',
    'skill.history'         => 'History',
    'skill.investigation'   => 'Investigation',
    'skill.nature'          => 'Nature',
    'skill.religion'        => 'Religion',
    'skill.animal_handling' => 'Animal Handling',
    'skill.insight'         => 'Insight',
    'skill.medicine'        => 'Medicine',
    'skill.perception'      => 'Perception',
    'skill.survival'        => 'Survival',
    'skill.deception'       => 'Deception',
    'skill.intimidation'    => 'Intimidation',
    'skill.performance'     => 'Performance',
    'skill.persuasion'      => 'Persuasion',

    // Talents
    'talents'             => 'Talents',
    'talent'              => 'Talent',
    'select_talent'       => '– select a talent –',
    'remove_talent'       => 'Remove talent',
    'field.description'   => 'Description',
    'field.action_type'   => 'Action type',
    'field.range'         => 'Range',
    'field.attribute'     => 'Attribute',
    'field.attack_type'   => 'Attack type',
    'field.damage_type'   => 'Damage type',
    'field.magic_effect'  => 'Magic effect',
    'field.projectile'    => 'Projectile',
    'field.uses'          => 'Uses',
    'field.uses_spent'    => 'Uses spent',
    'field.duration'      => 'Duration',
    'field.precondition'  => 'Precondition',
    'field.components'    => 'Components',
    'field.material'      => 'Material',

    // Account pages: common
    'email'               => 'Email',
    'password'            => 'Password',
    'repeat_password'     => 'Repeat password',
    'username'            => 'Username',
    'sign_in'             => 'Sign in',
    'register'            => 'Register',
    'log_out'             => 'Log out',
    'forgot_password'     => 'Forgot password?',
    'no_account'          => 'No account yet?',
    'back_to_sign_in'     => 'Back to sign in',
    'resend_link'         => 'Resend verification link',
    'reset_button'        => 'Reset',
    'err.invalid_session'   => 'Invalid session. Please try again.',
    'err.invalid_email'     => 'Please enter a valid email.',
    'err.password_short'    => 'Password must be at least 8 characters.',
    'err.password_mismatch' => 'Passwords do not match.',

    // login.php
    'login.title'         => 'Sign in',
    'login.please_sign_in' => 'Please sign in to continue.',
    'login.invalid'       => 'Invalid email or password.',
    'login.verify_first'  => 'Please verify your email first. You can resend the link below.',

    // register.php
    'register.title'      => 'Create account',
    'register.have_account' => 'Already have an account?',
    'register.username_hint' => '3 to 40 characters: A-Z, a-z, 0-9, . _ -',
    'register.username_rule' => 'Username must be 3 to 40 characters: A-Z, a-z, 0-9, ".", "_" or "-".',
    'register.username_taken' => 'Username already taken. Please try another.',
    'register.sent'       => 'If your email is valid, we have sent a verification link. Please check your inbox.',

    // ForgottenPW.php and reset.php
    'forgot.title'        => 'Forgot password',
    'forgot.sent'         => 'If this email is registered, we have sent a reset link. Please check your inbox.',
    'reset.title'         => 'Reset password',
    'reset.done'          => 'Your password was reset. You can now sign in.',
    'reset.link_invalid'  => 'The link is not valid or has expired. Please request a new link.',

    // verify.php and resend.php
    'verify.title'        => 'Email verification',
    'verify.ok'           => 'Your email has been verified.',
    'verify.failed'       => 'Verification failed or the link has expired. You can request a new link below.',
    'resend.title'        => 'Verification link',
    'resend.info'         => 'If your email exists and is not verified, we have sent a new verification link.',

    // dashboard.php
    'dashboard.title'     => 'Dashboard',
    'dashboard.welcome'   => 'Welcome,',
    'dashboard.role'      => 'Your role:',

    // characteroverview.php
    'overview.title'      => 'Your characters',
    'overview.create'     => 'Create',
    'overview.no_characters' => 'You have no characters yet.',
    'overview.modal_title' => 'How do you want to create your character?',
    'overview.sandbox_text' => 'Sandbox mode: you enter each value yourself.',
    'overview.railroad_text' => 'Railroad mode: you enter the values with the help of the 5e rules.',
    'overview.sandbox'    => 'Sandbox',
    'overview.railroad'   => 'Railroad',

    // Emails
    'mail.hello'          => 'Hello,',
    'mail.link_expires'   => 'This link expires in 24 hours.',
    'mail.verify.subject' => 'Verify your email',
    'mail.verify.body'    => 'Please verify your email with the link below:',
    'mail.exists.subject' => 'You already have an account',
    'mail.exists.body'    => 'Somebody tried to register with this email address. You already have an account.',
    'mail.exists.sign_in' => 'Sign in here:',
    'mail.exists.forgot'  => 'If you do not know your password:',
    'mail.exists.ignore'  => 'If this was not you, you can ignore this email. Your account does not change.',
    'mail.reset.subject'  => 'Reset your password',
    'mail.reset.body'     => 'Please reset your password with the link below:',
    'mail.reset.ignore'   => 'If you did not ask for this, ignore this email. Your password does not change.',
];
