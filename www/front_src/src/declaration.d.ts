interface Content {
  content: string;
  wrapper: string;
}

declare module '*.scss' {
  const content: Content;
  export default content;
}
