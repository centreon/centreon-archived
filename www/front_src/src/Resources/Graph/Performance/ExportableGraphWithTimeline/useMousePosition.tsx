import * as React from 'react';

type MousePosition = [number, number] | null;

interface MousePositionState {
  mousePosition: MousePosition;
  setMousePosition: React.Dispatch<React.SetStateAction<MousePosition>>;
}

const useMousePosition = (): MousePositionState => {
  const [mousePosition, setMousePosition] = React.useState<MousePosition>(null);

  return {
    mousePosition,
    setMousePosition,
  };
};

export default useMousePosition;

export const MousePositionContext =
  React.createContext<MousePositionState | undefined>(undefined);

export const useMousePositionContext = (): MousePositionState =>
  React.useContext(MousePositionContext) as MousePositionState;
