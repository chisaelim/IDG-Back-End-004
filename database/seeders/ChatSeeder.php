<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users
        $main = User::where('email', 'chisae0123@gmail.com')->first();
        $alice = User::where('email', 'alice@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();
        $charlie = User::where('email', 'charlie@example.com')->first();
        $diana = User::where('email', 'diana@example.com')->first();
        $edward = User::where('email', 'edward@example.com')->first();

        if (!$main || !$alice || !$bob || !$charlie || !$diana || !$edward) {
            $this->command->error('Users not found. Please run UserSeeder first.');
            return;
        }

        // 1. Personal Chat: Developer & Alice
        $personalChat1 = Chat::create([
            'name' => null,
            'type' => 'personal',
        ]);

        ChatMember::create(['chat_id' => $personalChat1->id, 'user_id' => $main->id, 'role' => 'admin']);
        ChatMember::create(['chat_id' => $personalChat1->id, 'user_id' => $alice->id, 'role' => 'member']);

        $messages1 = [
            ['user' => $alice, 'content' => 'Hey Developer! How are you doing?', 'time' => now()->subHours(5)],
            ['user' => $main, 'content' => 'Hi Alice! I\'m doing great, thanks for asking!', 'time' => now()->subHours(4)->subMinutes(45)],
            ['user' => $alice, 'content' => 'That\'s wonderful! Are you free this weekend?', 'time' => now()->subHours(4)->subMinutes(30)],
            ['user' => $main, 'content' => 'Yes, I should be. What did you have in mind?', 'time' => now()->subHours(4)->subMinutes(15)],
            ['user' => $alice, 'content' => 'Maybe we could grab coffee and catch up?', 'time' => now()->subHours(4)],
            ['user' => $main, 'content' => 'Sounds perfect! How about Saturday at 2 PM?', 'time' => now()->subHours(3)->subMinutes(30)],
            ['user' => $alice, 'content' => 'Saturday at 2 PM works great for me!', 'time' => now()->subHours(3)],
            ['user' => $main, 'content' => 'Awesome! See you then! 😊', 'time' => now()->subHours(2)->subMinutes(45)],
        ];

        foreach ($messages1 as $msg) {
            ChatMessage::create([
                'chat_id' => $personalChat1->id,
                'user_id' => $msg['user']->id,
                'content' => $msg['content'],
                'type' => 'text',
                'created_at' => $msg['time'],
                'updated_at' => $msg['time'],
            ]);
        }

        // 2. Personal Chat: Developer & Bob
        $personalChat2 = Chat::create([
            'name' => null,
            'type' => 'personal',
        ]);

        ChatMember::create(['chat_id' => $personalChat2->id, 'user_id' => $main->id, 'role' => 'admin']);
        ChatMember::create(['chat_id' => $personalChat2->id, 'user_id' => $bob->id, 'role' => 'member']);

        $messages2 = [
            ['user' => $bob, 'content' => 'Hi Developer, did you finish the project report?', 'time' => now()->subHours(2)],
            ['user' => $main, 'content' => 'Yes, I just submitted it this morning!', 'time' => now()->subHours(1)->subMinutes(45)],
            ['user' => $bob, 'content' => 'Great! Can you send me a copy?', 'time' => now()->subHours(1)->subMinutes(30)],
            ['user' => $main, 'content' => 'Sure, I\'ll email it to you right now.', 'time' => now()->subHours(1)],
            ['user' => $bob, 'content' => 'Thanks! You\'re a lifesaver!', 'time' => now()->subMinutes(45)],
        ];

        foreach ($messages2 as $msg) {
            ChatMessage::create([
                'chat_id' => $personalChat2->id,
                'user_id' => $msg['user']->id,
                'content' => $msg['content'],
                'type' => 'text',
                'created_at' => $msg['time'],
                'updated_at' => $msg['time'],
            ]);
        }

        // 3. Group Chat: Project Team
        $groupChat1 = Chat::create([
            'name' => 'Project Team Alpha',
            'type' => 'group',
        ]);

        ChatMember::create(['chat_id' => $groupChat1->id, 'user_id' => $main->id, 'role' => 'admin']);
        ChatMember::create(['chat_id' => $groupChat1->id, 'user_id' => $alice->id, 'role' => 'member']);
        ChatMember::create(['chat_id' => $groupChat1->id, 'user_id' => $bob->id, 'role' => 'member']);
        ChatMember::create(['chat_id' => $groupChat1->id, 'user_id' => $charlie->id, 'role' => 'admin']);

        $messages3 = [
            ['user' => $main, 'content' => 'Good morning team! Let\'s discuss today\'s agenda.', 'time' => now()->subHours(6)],
            ['user' => $alice, 'content' => 'Morning! I\'ve prepared the presentation slides.', 'time' => now()->subHours(5)->subMinutes(45)],
            ['user' => $bob, 'content' => 'Great work Alice! I\'ll review them before the meeting.', 'time' => now()->subHours(5)->subMinutes(30)],
            ['user' => $charlie, 'content' => 'I\'ve finished the backend API implementation.', 'time' => now()->subHours(5)],
            ['user' => $main, 'content' => 'Excellent! We\'re making great progress.', 'time' => now()->subHours(4)->subMinutes(45)],
            ['user' => $alice, 'content' => 'Should we schedule a demo for the client?', 'time' => now()->subHours(4)->subMinutes(30)],
            ['user' => $main, 'content' => 'Yes, let\'s aim for Friday afternoon.', 'time' => now()->subHours(4)],
            ['user' => $bob, 'content' => 'I\'ll send out the meeting invite.', 'time' => now()->subHours(3)->subMinutes(45)],
            ['user' => $charlie, 'content' => 'Perfect! I\'ll prepare the demo environment.', 'time' => now()->subHours(3)],
            ['user' => $main, 'content' => 'Thanks everyone for the hard work! 💪', 'time' => now()->subHours(2)->subMinutes(30)],
        ];

        foreach ($messages3 as $msg) {
            ChatMessage::create([
                'chat_id' => $groupChat1->id,
                'user_id' => $msg['user']->id,
                'content' => $msg['content'],
                'type' => 'text',
                'created_at' => $msg['time'],
                'updated_at' => $msg['time'],
            ]);
        }

        // 4. Group Chat: Study Group
        $groupChat2 = Chat::create([
            'name' => 'Laravel Study Group',
            'type' => 'group',
        ]);

        ChatMember::create(['chat_id' => $groupChat2->id, 'user_id' => $main->id, 'role' => 'admin']);
        ChatMember::create(['chat_id' => $groupChat2->id, 'user_id' => $diana->id, 'role' => 'member']);
        ChatMember::create(['chat_id' => $groupChat2->id, 'user_id' => $edward->id, 'role' => 'member']);

        $messages4 = [
            ['user' => $main, 'content' => 'Welcome to the Laravel Study Group!', 'time' => now()->subDays(2)],
            ['user' => $diana, 'content' => 'Thanks for creating this group!', 'time' => now()->subDays(2)->addHours(1)],
            ['user' => $edward, 'content' => 'Excited to learn together!', 'time' => now()->subDays(2)->addHours(2)],
            ['user' => $main, 'content' => 'Today we\'ll cover Eloquent relationships.', 'time' => now()->subDays(1)],
            ['user' => $diana, 'content' => 'I\'ve been struggling with many-to-many relationships.', 'time' => now()->subDays(1)->addHours(1)],
            ['user' => $main, 'content' => 'No worries! Let me share some examples.', 'time' => now()->subDays(1)->addHours(2)],
            ['user' => $edward, 'content' => 'The documentation on pivot tables is really helpful.', 'time' => now()->subDays(1)->addHours(3)],
            ['user' => $main, 'content' => 'Exactly! And don\'t forget about eager loading to avoid N+1 queries.', 'time' => now()->subHours(12)],
            ['user' => $diana, 'content' => 'That makes so much sense now!', 'time' => now()->subHours(10)],
            ['user' => $edward, 'content' => 'What topic are we covering next?', 'time' => now()->subHours(8)],
            ['user' => $main, 'content' => 'Next session will be about API resources and authentication.', 'time' => now()->subHours(6)],
        ];

        foreach ($messages4 as $msg) {
            ChatMessage::create([
                'chat_id' => $groupChat2->id,
                'user_id' => $msg['user']->id,
                'content' => $msg['content'],
                'type' => 'text',
                'created_at' => $msg['time'],
                'updated_at' => $msg['time'],
            ]);
        }

        // 5. Personal Chat: Developer & Charlie (Recent)
        $personalChat3 = Chat::create([
            'name' => null,
            'type' => 'personal',
        ]);

        ChatMember::create(['chat_id' => $personalChat3->id, 'user_id' => $main->id, 'role' => 'admin']);
        ChatMember::create(['chat_id' => $personalChat3->id, 'user_id' => $charlie->id, 'role' => 'member']);

        $messages5 = [
            ['user' => $charlie, 'content' => 'Hey, do you have a minute?', 'time' => now()->subMinutes(30)],
            ['user' => $main, 'content' => 'Sure! What\'s up?', 'time' => now()->subMinutes(28)],
            ['user' => $charlie, 'content' => 'I need your opinion on the database schema.', 'time' => now()->subMinutes(25)],
            ['user' => $main, 'content' => 'Happy to help! Send it over.', 'time' => now()->subMinutes(23)],
            ['user' => $charlie, 'content' => 'Thanks! I\'ll share it in a few minutes.', 'time' => now()->subMinutes(20)],
        ];

        foreach ($messages5 as $msg) {
            ChatMessage::create([
                'chat_id' => $personalChat3->id,
                'user_id' => $msg['user']->id,
                'content' => $msg['content'],
                'type' => 'text',
                'created_at' => $msg['time'],
                'updated_at' => $msg['time'],
            ]);
        }

        // Update chat timestamps to match last message
        $personalChat1->update(['updated_at' => now()->subHours(2)->subMinutes(45)]);
        $personalChat2->update(['updated_at' => now()->subMinutes(45)]);
        $groupChat1->update(['updated_at' => now()->subHours(2)->subMinutes(30)]);
        $groupChat2->update(['updated_at' => now()->subHours(6)]);
        $personalChat3->update(['updated_at' => now()->subMinutes(20)]);

        $this->command->info('Chat data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 3 Personal chats (Developer with Alice, Bob, Charlie)');
        $this->command->info('- 2 Group chats (Project Team Alpha, Laravel Study Group)');
        $this->command->info('- Multiple text messages in each chat');
    }
}
