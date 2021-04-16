import * as React from 'react';

type MousePosition = [number, number] | null;

interface UseMousePosition {
  mousePosition: MousePosition;
  setMousePosition: React.Dispatch<React.SetStateAction<MousePosition>>;
}

const useMousePosition = (): UseMousePosition => {
  const [mousePosition, setMousePosition] = React.useState<MousePosition>(null);

  return {
    mousePosition,
    setMousePosition,
  };
};

export default useMousePosition;

export const MousePositionContext = React.createContext<
  UseMousePosition | undefined
>(undefined);

export const useMousePositionContext = (): UseMousePosition =>
  React.useContext(MousePositionContext) as UseMousePosition;
