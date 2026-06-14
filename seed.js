const { createClient } = require('@supabase/supabase-js');
require('dotenv').config();

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error('Missing Supabase environment variables! Cannot run seed.');
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey, {
  auth: { autoRefreshToken: false, persistSession: false }
});

const defaultPassword = 'password123';
const adminPassword = 'admin123';

const usersToCreate = [
  { fullname: 'System Administrator', email: 'admin@hostel.com', password: adminPassword, gender: 'Male', level: 400, medical_status: false, allocation_status: 'not_requested' },
  { fullname: 'John Adebayo', email: 'john@student.edu', password: defaultPassword, gender: 'Male', level: 400, medical_status: true, allocation_status: 'pending' },
  { fullname: 'Mary Okonkwo', email: 'mary@student.edu', password: defaultPassword, gender: 'Female', level: 300, medical_status: false, allocation_status: 'pending' },
  { fullname: 'David Chukwu', email: 'david@student.edu', password: defaultPassword, gender: 'Male', level: 200, medical_status: false, allocation_status: 'pending' },
  { fullname: 'Grace Emeka', email: 'grace@student.edu', password: defaultPassword, gender: 'Female', level: 400, medical_status: true, allocation_status: 'pending' },
  { fullname: 'Samuel Ojo', email: 'samuel@student.edu', password: defaultPassword, gender: 'Male', level: 100, medical_status: false, allocation_status: 'not_requested' }
];

const hostelsToCreate = [
  { id: '11111111-1111-1111-1111-111111111111', name: 'Independence Hall', gender_category: 'Male', total_rooms: 2 },
  { id: '22222222-2222-2222-2222-222222222222', name: 'Queen Elizabeth Hall', gender_category: 'Female', total_rooms: 2 },
];

const roomsToCreate = [
  { hostel_id: '11111111-1111-1111-1111-111111111111', room_number: 'A101', capacity: 4, occupied_slots: 0 },
  { hostel_id: '11111111-1111-1111-1111-111111111111', room_number: 'A102', capacity: 2, occupied_slots: 0 },
  { hostel_id: '22222222-2222-2222-2222-222222222222', room_number: 'A101', capacity: 4, occupied_slots: 0 },
  { hostel_id: '22222222-2222-2222-2222-222222222222', room_number: 'A102', capacity: 2, occupied_slots: 0 },
];

async function seed() {
  console.log('Starting seed process...');

  // 1. Seed Users
  for (const user of usersToCreate) {
    console.log(`Creating user: ${user.email}`);
    const { data: authData, error: authError } = await supabaseAdmin.auth.admin.createUser({
      email: user.email,
      password: user.password,
      email_confirm: true // bypass email confirmation
    });

    if (authError) {
      console.error(`Error creating auth user ${user.email}:`, authError.message);
      continue;
    }

    if (authData.user) {
      const { error: dbError } = await supabaseAdmin.from('students').insert({
        id: authData.user.id,
        email: user.email,
        fullname: user.fullname,
        gender: user.gender,
        level: user.level,
        medical_status: user.medical_status,
        allocation_status: user.allocation_status
      });

      if (dbError) {
        console.error(`Error inserting into students table for ${user.email}:`, dbError.message);
      } else {
        console.log(`Successfully seeded ${user.email}`);
      }
    }
  }

  // 2. Seed Hostels and Rooms (Optional, but good for testing)
  console.log('Seeding hostels...');
  for (const hostel of hostelsToCreate) {
    await supabaseAdmin.from('hostels').upsert(hostel);
  }

  console.log('Seeding rooms...');
  for (const room of roomsToCreate) {
    await supabaseAdmin.from('rooms').insert(room);
  }

  console.log('Seed process completed!');
}

seed();
