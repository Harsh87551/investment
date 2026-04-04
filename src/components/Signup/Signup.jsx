import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Signup.css';
import { signupUser } from '../../services/authService';

export default function Signup() {

  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    password: ''
  });

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {

    e.preventDefault();

    try {

      const res = await signupUser(formData);

      console.log(res.data);

      // Try to store the token if the backend returns it upon registration
      if (res.data.token) {
        localStorage.setItem("token", res.data.token);
      }
      
      localStorage.setItem("isLoggedIn", "true");
      // Store user data without password for security
      const { password, ...safeData } = formData;
      localStorage.setItem("registeredUser", JSON.stringify(res.data.user || safeData));

      // alert("Signup Successful");

      navigate("/dashboard");

    } catch (error) {

      console.log(error);
      alert("Signup failed");

    }

  };

  return (
    <div className="auth-container">

      <div className="hero-heading">
        <h1>Investment Plans</h1>
        <p>Unlock your financial future today.</p>
      </div>

      <h2>Create an Account</h2>

      <form onSubmit={handleSubmit} className="auth-form">

        <div className="form-group">
          <label>Name</label>

          <input
            type="text"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            placeholder="Enter your name"
          />

        </div>

        <div className="form-group">
          <label>Email</label>

          <input
            type="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
            placeholder="Enter your email"
          />

        </div>

        <div className="form-group">
          <label>Phone Number</label>

          <input
            type="tel"
            name="phone"
            value={formData.phone}
            onChange={handleChange}
            required
            placeholder="Enter your phone number"
          />

        </div>

        <div className="form-group">
          <label>Password</label>

          <input
            type="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
            placeholder="Enter your password"
          />

        </div>

        <button type="submit" className="auth-button">
          Sign Up
        </button>

      </form>

      <p className="auth-link">
        Already have an account? <Link to="/login">Log in</Link>
      </p>

    </div>
  );
}