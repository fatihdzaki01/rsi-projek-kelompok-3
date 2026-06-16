<template>
  <div class="selector">
    <select
      class="select-input"
      :value="modelValue?.id"
      @change="onChange"
    >
      <option v-for="c in campaigns" :key="c.id" :value="c.id">
        {{ c.name }}
      </option>
    </select>
  </div>
</template>

<script setup>
const props = defineProps({ campaigns: Array, modelValue: Object })
const emit  = defineEmits(['update:modelValue'])

const onChange = (e) => {
  const found = props.campaigns.find(c => c.id === Number(e.target.value))
  emit('update:modelValue', found)
}
</script>

<style scoped>
.selector { position: relative; }

.select-input {
  appearance: none;
  background: white;
  border: 1.5px solid #e0d8d0;
  border-radius: 10px;
  padding: 10px 40px 10px 16px;
  font-size: 14px; font-weight: 500;
  color: #1a1a2e; min-width: 280px; cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
}

.select-input:focus { outline: none; border-color: #4a3728; }
</style>