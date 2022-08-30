export interface FactorsData {
  currentFactor: number;
  simulatedFactor: number;
}

export interface CustomFactorsData extends FactorsData {
  isResizing: boolean;
}
