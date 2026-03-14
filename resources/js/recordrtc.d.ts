declare module 'recordrtc' {
    const RecordRTC: {
        RecordRTCPromisesHandler: new (
            stream: MediaStream,
            options: Record<string, unknown>,
        ) => unknown;
    };
    export default RecordRTC;
}
