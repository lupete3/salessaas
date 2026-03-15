import AsyncStorage from '@react-native-async-storage/async-storage';

export const TASKS_STORAGE_KEY = 'user_tasks';

export interface Task {
  id: string; // ID local (timestamp ou uuid)
  server_id?: string | null; // ID venant de Laravel
  title: string;
  description: string;
  is_synced: boolean; // 0 ou 1
  updated_at: string; // ISO string pour comparer les dates
}

export const StorageService = {
  // Save tasks to storage
  saveTasks: async (tasks: Task[]) => {
    try {
      const jsonValue = JSON.stringify(tasks);
      await AsyncStorage.setItem(TASKS_STORAGE_KEY, jsonValue);
    } catch (e) {
      console.error('Error saving tasks:', e);
    }
  },

  // Load tasks from storage
  loadTasks: async (): Promise<Task[]> => {
    try {
      const jsonValue = await AsyncStorage.getItem(TASKS_STORAGE_KEY);
      return jsonValue != null ? JSON.parse(jsonValue) : [];
    } catch (e) {
      console.error('Error loading tasks:', e);
      return [];
    }
  },

  // Clear all tasks (useful for testing)
  clearTasks: async () => {
    try {
      await AsyncStorage.removeItem(TASKS_STORAGE_KEY);
    } catch (e) {
      console.error('Error clearing tasks:', e);
    }
  },

  // --- Helpers for SyncService ---

  // Update a local task with Server ID and mark as synced
  updateTaskAsSynced: async (localId: string, serverId: string) => {
    const tasks = await StorageService.loadTasks();
    const updated = tasks.map(t => {
      if (t.id === localId) {
        return { ...t, is_synced: true, server_id: serverId };
      }
      return t;
    });
    await StorageService.saveTasks(updated);
  },

  // Merge server tasks into local storage
  // Strategy: If server_id exists locally, update it. If not, add it.
  // Note: This is a simplified merge logic.
  mergeServerTasks: async (serverTasks: any[]) => {
    const localTasks = await StorageService.loadTasks();
    let hasChanges = false;

    // Create a map of server_ids for easy lookup
    const localMapByServerId = new Map(
      localTasks.filter(t => t.server_id).map(t => [t.server_id, t])
    );

    const newLocalTasks = [...localTasks];

    serverTasks.forEach(sTask => {
      // Assuming server sends: id, title, description, updated_at
      if (localMapByServerId.has(sTask.id)) {
        // Task exists locally - Update it?
        // (Here we blindly trust server for simplicity, or check updated_at)
        const existingIndex = newLocalTasks.findIndex(t => t.server_id === sTask.id);
        if (existingIndex > -1) {
          newLocalTasks[existingIndex] = {
             ...newLocalTasks[existingIndex],
             title: sTask.title,
             description: sTask.description,
             is_synced: true,
             updated_at: sTask.updated_at
          };
          hasChanges = true;
        }
      } else {
        // New task from server
        // Check if we have a local task that looks identical but unsynced? (Deduplication)
        // For now, just add it.
        newLocalTasks.push({
          id: Date.now().toString() + Math.random(), // New local ID
          server_id: sTask.id,
          title: sTask.title,
          description: sTask.description,
          is_synced: true,
          updated_at: sTask.updated_at
        });
        hasChanges = true;
      }
    });

    if (hasChanges) {
      await StorageService.saveTasks(newLocalTasks);
    }
  }
};
