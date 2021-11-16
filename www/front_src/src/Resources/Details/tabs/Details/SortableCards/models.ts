import { DetailCardLine } from '../DetailsCard/cards';

export interface CardsLayout extends DetailCardLine {
  id: string;
  width: number;
}

export enum ExpandAction {
  add,
  remove,
}

export interface ChangeExpandedCardsProps {
  action: ExpandAction;
  card: string;
}
