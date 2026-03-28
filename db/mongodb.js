// Example MongoDB Document for User Profile
const userProfileExample = {
  user_id: 1, // References MySQL users.id
  name: "John Doe",
  age: 28,
  dob: "1995-08-15",
  mobile: "1234567890",
  profile_pic: "https://example.com/pic.jpg",
  created_at: new Date()
};

// Database: user_system
// Collection: profiles
// db.profiles.insertOne(userProfileExample);