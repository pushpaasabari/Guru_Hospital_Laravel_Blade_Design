const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('guruDesktop', {
  getState: () => ipcRenderer.invoke('desktop:get-state'),
  checkLiveServer: (url) => ipcRenderer.invoke('desktop:check-live', url),
  launchOffline: (opts) => ipcRenderer.invoke('desktop:launch-offline', opts),
  launchLive: (opts) => ipcRenderer.invoke('desktop:launch-live', opts),
  openDataFolder: () => ipcRenderer.invoke('desktop:open-data-folder'),
  onStatusUpdate: (callback) => {
    const handler = (_event, msg) => callback(msg);
    ipcRenderer.on('desktop:status', handler);
    return () => ipcRenderer.removeListener('desktop:status', handler);
  },
});
