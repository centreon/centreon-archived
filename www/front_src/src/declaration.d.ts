interface Content {
  [x: string]: string | undefined;
  content: string;
  wrapper: string;
}

declare module '*.scss' {
  const content: Content;
  export default content;
}

declare module '*.svg' {
  const content;
  export const ReactComponent;
  export default content;
}
