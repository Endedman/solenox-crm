let webamp;  // global variable

function playMusic(event) {
  event.preventDefault();

  const Webamp = window.Webamp;

  // If webamp already exists, dispose it before creating a new one.
  if (webamp) {
    webamp.dispose();
  }

  webamp = new Webamp({
    initialTracks: [
      {
        url: event.target.href,
        metaData: {
          title: event.target.textContent
        }
      },
    ],
    __butterchurnOptions: {
      importButterchurn: () => Promise.resolve(window.butterchurn),
      getPresets: () => {
        const presets = window.butterchurnPresets.getPresets();
        return Object.keys(presets).map((name) => {
          return {
            name,
            butterchurnPresetObject: presets[name]
          };
        });
      },
      butterchurnOpen: true
    },
    __initialWindowLayout: {
      main: { position: { x: 0, y: 0 } },
      equalizer: { position: { x: 0, y: 116 } },
      playlist: { position: { x: 0, y: 232 }, size: [0, 4] },
      milkdrop: { position: { x: 275, y: 0 }, size: [7, 12] }
    }
  });

  webamp.renderWhenReady(document.getElementById('player'));
}