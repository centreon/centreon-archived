import { useTranslation } from 'react-i18next';
import { isEmpty, pipe, reject, slice } from 'ramda';

import { Typography, Divider, CardActions, Button, Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import { CreateCSSProperties } from '@mui/styles';

import { getStatusColors } from '@centreon/ui';

import { labelMore, labelLess } from '../../../translatedLabels';

import Card from './Card';
import { ChangeExpandedCardsProps, ExpandAction } from './SortableCards/models';

const Line = (line, index): JSX.Element => (
  <Typography component="p" key={`${line}-${index}`} variant="body2">
    {line}
  </Typography>
);

const useStyles = makeStyles<Theme, { severityCode?: number }>((theme) => {
  const getStatusBackgroundColor = (severityCode): string =>
    getStatusColors({
      severityCode,
      theme,
    }).backgroundColor;

  return {
    card: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && {
        borderColor: getStatusBackgroundColor(severityCode),
        borderStyle: 'solid',
        borderWidth: 2,
      }),
    }),
    title: ({ severityCode }): CreateCSSProperties => ({
      ...(severityCode && { color: getStatusBackgroundColor(severityCode) }),
    }),
  };
});

interface Props {
  changeExpandedCards: (props: ChangeExpandedCardsProps) => void;
  content: string;
  expandedCard: boolean;
  severityCode?: number;
  title: string;
}

const ExpandableCard = ({
  title,
  content,
  severityCode,
  expandedCard,
  changeExpandedCards,
}: Props): JSX.Element => {
  const classes = useStyles({ severityCode });
  const { t } = useTranslation();

  const lines = content.split(/\n|\\n/);
  const threeFirstLines = lines.slice(0, 3);
  const lastLines = pipe(slice(3, lines.length), reject(isEmpty))(lines);

  const toggleOutputExpanded = (): void => {
    if (expandedCard) {
      changeExpandedCards({ action: ExpandAction.remove, card: title });

      return;
    }

    changeExpandedCards({ action: ExpandAction.add, card: title });
  };

  return (
    <Card className={classes.card}>
      <Typography
        gutterBottom
        className={classes.title}
        color="textSecondary"
        variant="subtitle2"
      >
        {title}
      </Typography>
      {threeFirstLines.map(Line)}
      {expandedCard && lastLines.map(Line)}
      {lastLines.length > 0 && (
        <>
          <Divider />
          <CardActions>
            <Button color="primary" size="small" onClick={toggleOutputExpanded}>
              {expandedCard ? t(labelLess) : t(labelMore)}
            </Button>
          </CardActions>
        </>
      )}
    </Card>
  );
};

export default ExpandableCard;
