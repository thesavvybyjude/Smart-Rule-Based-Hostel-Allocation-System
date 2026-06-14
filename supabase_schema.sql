-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Table: students
CREATE TABLE IF NOT EXISTS students (
  id UUID PRIMARY KEY, -- Linked to auth.users
  email TEXT UNIQUE NOT NULL,
  fullname TEXT NOT NULL,
  gender TEXT CHECK (gender IN ('Male', 'Female')) NOT NULL,
  level INT NOT NULL,
  medical_status BOOLEAN DEFAULT FALSE,
  allocation_status TEXT CHECK (allocation_status IN ('not_requested', 'pending', 'allocated', 'waitlisted')) DEFAULT 'not_requested',
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Table: hostels
CREATE TABLE IF NOT EXISTS hostels (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL,
  gender_category TEXT CHECK (gender_category IN ('Male', 'Female')) NOT NULL,
  total_rooms INT
);

-- 3. Table: rooms
CREATE TABLE IF NOT EXISTS rooms (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  hostel_id UUID REFERENCES hostels(id) ON DELETE CASCADE,
  room_number TEXT NOT NULL,
  capacity INT NOT NULL,
  occupied_slots INT DEFAULT 0
);

-- 4. Table: allocations
CREATE TABLE IF NOT EXISTS allocations (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  student_id UUID REFERENCES students(id) ON DELETE CASCADE,
  room_id UUID REFERENCES rooms(id) ON DELETE CASCADE,
  allocated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Table: feedback
CREATE TABLE IF NOT EXISTS feedback (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  student_id UUID REFERENCES students(id) ON DELETE CASCADE,
  subject TEXT NOT NULL,
  message TEXT NOT NULL,
  type TEXT CHECK (type IN ('bug', 'error', 'suggestion', 'other')) NOT NULL,
  status TEXT CHECK (status IN ('pending', 'acknowledged', 'resolved', 'closed')) DEFAULT 'pending',
  admin_response TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Configure Row Level Security (RLS)

-- Enable RLS on all tables
ALTER TABLE students ENABLE ROW LEVEL SECURITY;
ALTER TABLE hostels ENABLE ROW LEVEL SECURITY;
ALTER TABLE rooms ENABLE ROW LEVEL SECURITY;
ALTER TABLE allocations ENABLE ROW LEVEL SECURITY;
ALTER TABLE feedback ENABLE ROW LEVEL SECURITY;

-- Create policies for 'students' table
-- A student can insert their own record upon registration
CREATE POLICY student_insert ON students FOR INSERT WITH CHECK (auth.uid() = id);
-- A student can read and update ONLY their own record
CREATE POLICY student_select ON students FOR SELECT USING (auth.uid() = id);
CREATE POLICY student_update ON students FOR UPDATE USING (auth.uid() = id);

-- Create policies for 'allocations' table
-- A student can view their own allocation
CREATE POLICY allocation_select ON allocations FOR SELECT USING (auth.uid() = student_id);

-- Create policies for 'feedback' table
-- A student can insert and view their own feedback
CREATE POLICY feedback_insert ON feedback FOR INSERT WITH CHECK (auth.uid() = student_id);
CREATE POLICY feedback_select ON feedback FOR SELECT USING (auth.uid() = student_id);

-- Create policies for public reading of hostels and rooms (needed for dashboard stats)
-- Or you can restrict this and only let admin view it. For now, let students view rooms/hostels
CREATE POLICY hostels_select ON hostels FOR SELECT USING (true);
CREATE POLICY rooms_select ON rooms FOR SELECT USING (true);

-- ADMIN POLICIES --
-- Admin can do everything on all tables
CREATE POLICY admin_students_all ON students FOR ALL USING (auth.jwt() ->> 'email' = 'admin@hostel.com');
CREATE POLICY admin_hostels_all ON hostels FOR ALL USING (auth.jwt() ->> 'email' = 'admin@hostel.com');
CREATE POLICY admin_rooms_all ON rooms FOR ALL USING (auth.jwt() ->> 'email' = 'admin@hostel.com');
CREATE POLICY admin_allocations_all ON allocations FOR ALL USING (auth.jwt() ->> 'email' = 'admin@hostel.com');
CREATE POLICY admin_feedback_all ON feedback FOR ALL USING (auth.jwt() ->> 'email' = 'admin@hostel.com');

-- NOTE: The serverless functions use the SERVICE_ROLE_KEY, which completely bypasses RLS.
-- This means operations like running the allocation engine, resetting rooms, and getting all reports
-- will work fine from the backend API without needing special admin RLS policies.
