import bcrypt from "bcryptjs";

const run = async () => {
  const hashed =
    await bcrypt.hash("123456", 10);

  console.log(hashed);
};

run();