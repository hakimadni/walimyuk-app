<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const props = defineProps({
  musics: Array
})

const form = useForm({
  title: '',
  artist: '',
  file: null
})

function submit() {
  form.post('/background-music', {
    preserveScroll: true,
    onSuccess: () => form.reset()
  })
}

function deleteMusic(id) {
  if (confirm('Delete this music?')) {
    useForm({}).delete(`/background-music/${id}`)
  }
}
</script>

<template>
  <Head title="Music Library" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-serif text-2xl font-bold text-emerald-950">Music Library Manager</h2>
    </template>
    <div class="py-6 mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
      <Card>
        <CardHeader><CardTitle>Upload New Music</CardTitle></CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-4 max-w-md">
            <div>
              <label class="block text-sm font-medium mb-1">Title</label>
              <input v-model="form.title" type="text" class="w-full rounded border-slate-300" required>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Artist</label>
              <input v-model="form.artist" type="text" class="w-full rounded border-slate-300">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">MP3 File</label>
              <input @input="form.file = $event.target.files[0]" type="file" accept="audio/*" class="w-full" required>
            </div>
            <Button type="submit" :disabled="form.processing">Upload</Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader><CardTitle>Library</CardTitle></CardHeader>
        <CardContent>
          <div v-for="music in musics" :key="music.id" class="flex justify-between items-center p-3 border-b">
            <div>
              <p class="font-bold">{{ music.title }}</p>
              <p class="text-xs text-slate-500">{{ music.artist }}</p>
              <audio controls :src="music.file_path" class="mt-2 h-8"></audio>
            </div>
            <Button variant="destructive" size="sm" @click="deleteMusic(music.id)">Delete</Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
