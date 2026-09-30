<template>
  <div class="home-page">
    <div class="card">
      <p class="eyebrow">Test page</p>
      <h1>Home</h1>
      <p>
        This is a simple test page for the frontend application.
      </p>
      <button @click="handleClick">Click me</button>
    </div>
  </div>
</template>

<script setup>
const handleClick = () => {
  alert('Test page clicked!')
}
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f7ff, #e8eefc);
  padding: 24px;
}

.card {
  max-width: 420px;
  width: 100%;
  background: white;
  border-radius: 18px;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
  padding: 32px 28px;
  text-align: center;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  color: #5b6bff;
  font-weight: 700;
}

h1 {
  margin: 0 0 12px;
  color: #1f2937;
  font-size: 2rem;
}

p {
  margin: 0 0 24px;
  color: #4b5563;
  line-height: 1.6;
}

button {
  border: none;
  border-radius: 10px;
  background: #4f46e5;
  color: white;
  padding: 12px 20px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

button:hover {
  opacity: 0.92;
}
</style>
