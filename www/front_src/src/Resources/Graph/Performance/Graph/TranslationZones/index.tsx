import * as React from 'react';

import { equals, not } from 'ramda';

import ArrowBackIosIcon from '@material-ui/icons/ArrowBackIos';
import ArrowForwardIosIcon from '@material-ui/icons/ArrowForwardIos';

import { labelBackward, labelForward } from '../../../../translatedLabels';

import TranslationZone, { translationZoneWidth } from './Zone';
import TranslationIcon, { translationIconSize } from './Icon';

export enum TranslationDirection {
  backward,
  forward,
}

interface TranslationContextProps {
  graphHeight: number;
  graphWidth: number;
  marginLeft: number;
  marginTop: number;
  canNavigateInGraph: boolean;
  sendingGetGraphDataRequest: boolean;
  translate?: (direction: TranslationDirection) => void;
}

export const TranslationContext = React.createContext<
  TranslationContextProps | undefined
>(undefined);

export const useTranslationsContext = (): TranslationContextProps =>
  React.useContext(TranslationContext) as TranslationContextProps;

const Translations = (): JSX.Element | null => {
  const [
    directionHovered,
    setDirectionHovered,
  ] = React.useState<TranslationDirection | null>(null);

  const {
    graphWidth,
    sendingGetGraphDataRequest,
    canNavigateInGraph,
  } = useTranslationsContext();

  const hoverDirection = (direction: TranslationDirection | null) => () =>
    setDirectionHovered(direction);

  const getIconColor = (direction: TranslationDirection) =>
    sendingGetGraphDataRequest || not(equals(directionHovered, direction))
      ? 'disabled'
      : 'primary';

  if (not(canNavigateInGraph)) {
    return null;
  }

  return (
    <>
      <TranslationZone
        direction={TranslationDirection.backward}
        directionHovered={directionHovered}
        hoverDirection={hoverDirection}
      />
      <TranslationZone
        direction={TranslationDirection.forward}
        directionHovered={directionHovered}
        hoverDirection={hoverDirection}
      />
      <TranslationIcon
        xIcon={0}
        icon={
          <ArrowBackIosIcon
            color={getIconColor(TranslationDirection.backward)}
          />
        }
        direction={TranslationDirection.backward}
        hoverDirection={hoverDirection}
        ariaLabel={labelBackward}
      />
      <TranslationIcon
        xIcon={graphWidth + translationZoneWidth + translationIconSize}
        icon={
          <ArrowForwardIosIcon
            color={getIconColor(TranslationDirection.forward)}
          />
        }
        direction={TranslationDirection.forward}
        hoverDirection={hoverDirection}
        ariaLabel={labelForward}
      />
    </>
  );
};

export default Translations;
