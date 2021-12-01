import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';

import {
  Button,
  ButtonGroup,
  ButtonProps,
  makeStyles,
  useTheme,
} from '@material-ui/core';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import {
  labelForceToUseLowerCase,
  labelForceToUseNumbers,
  labelForceToUseSpecialCharacters,
  labelForceToUseUpperCase,
  labelGood,
  labelLowerCase,
  labelNumber,
  labelPasswordCases,
  labelSpecialCharacters,
  labelStrong,
  labelUpperCase,
  labelWeak,
} from '../../../translatedLabels';
import StrengthProgress from '../../../StrengthProgress';
import { getFields } from '../../utils';

import LabelWithTooltip from './LabelWithTooltip';

const activeButtonProps = {
  variant: 'contained',
} as ButtonProps;
const hasLowerCaseName = 'hasLowerCase';
const hasUpperCaseName = 'hasUpperCase';
const hasNumberName = 'hasNumber';
const hasSpecialCharacterName = 'hasSpecialCharacter';

const useStyles = makeStyles((theme) => ({
  caseButtonsContainer: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(0.5),
  },
  lowerCaseButton: {
    textTransform: 'none',
  },
}));

const CaseButtons = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const theme = useTheme();

  const { values, setFieldValue } = useFormikContext<FormikValues>();

  const selectCase = (caseName: string) => (): void => {
    setFieldValue(caseName, !values[caseName]);
  };

  const [hasLowerCase, hasUpperCase, hasNumber, hasSpecialCharacter] =
    React.useMemo(
      () =>
        getFields<boolean>({
          fields: [
            hasLowerCaseName,
            hasUpperCaseName,
            hasNumberName,
            hasSpecialCharacterName,
          ],
          object: values,
        }),
      [values],
    );

  const thresholds = React.useMemo(
    () => [
      { color: theme.palette.error.main, label: labelWeak, value: 2 },
      { color: theme.palette.warning.main, label: labelGood, value: 3 },
      { color: theme.palette.success.main, label: labelStrong, value: 4 },
    ],
    [theme],
  );

  const thresholdValue = [
    hasLowerCase,
    hasUpperCase,
    hasNumber,
    hasSpecialCharacter,
  ].filter(Boolean).length;

  return useMemoComponent({
    Component: (
      <div className={classes.caseButtonsContainer}>
        <ButtonGroup aria-label={t(labelPasswordCases)} size="small">
          <Button
            {...(hasLowerCase && activeButtonProps)}
            aria-label={t(labelForceToUseLowerCase)}
            className={classes.lowerCaseButton}
            color="primary"
            onClick={selectCase(hasLowerCaseName)}
          >
            <LabelWithTooltip
              label={labelLowerCase}
              tooltipLabel={labelForceToUseLowerCase}
            />
          </Button>
          <Button
            {...(hasUpperCase && activeButtonProps)}
            aria-label={t(labelForceToUseUpperCase)}
            color="primary"
            onClick={selectCase(hasUpperCaseName)}
          >
            <LabelWithTooltip
              label={labelUpperCase}
              tooltipLabel={labelForceToUseUpperCase}
            />
          </Button>
          <Button
            {...(hasNumber && activeButtonProps)}
            aria-label={t(labelForceToUseNumbers)}
            color="primary"
            onClick={selectCase(hasNumberName)}
          >
            <LabelWithTooltip
              label={labelNumber}
              tooltipLabel={labelForceToUseNumbers}
            />
          </Button>
          <Button
            {...(hasSpecialCharacter && activeButtonProps)}
            aria-label={t(labelForceToUseSpecialCharacters)}
            color="primary"
            onClick={selectCase(hasSpecialCharacterName)}
          >
            <LabelWithTooltip
              label={labelSpecialCharacters}
              tooltipLabel={labelForceToUseSpecialCharacters}
            />
          </Button>
        </ButtonGroup>
        <StrengthProgress
          max={4}
          thresholds={thresholds}
          value={thresholdValue}
        />
      </div>
    ),
    memoProps: [hasLowerCase, hasUpperCase, hasNumber, hasSpecialCharacter],
  });
};

export default CaseButtons;
