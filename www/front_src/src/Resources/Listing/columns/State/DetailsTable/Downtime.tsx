/* eslint-disable react/no-unused-prop-types */

import parse from 'html-react-parser';
import DOMPurify from 'dompurify';
import { useTranslation } from 'react-i18next';

import makeStyles from '@mui/styles/makeStyles';

import { ColumnType, useLocaleDateTimeFormat } from '@centreon/ui';

import {
  labelAuthor,
  labelFixed,
  labelStartTime,
  labelEndTime,
  labelComment,
} from '../../../../translatedLabels';

import DetailsTable, { getYesNoLabel } from '.';

const useStyles = makeStyles({
  comment: {
    display: 'block',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
});

interface DowntimeDetails {
  author_name: string;
  comment: string;
  end_time: Date | string;
  id: number;
  is_fixed: boolean;
  start_time: Date | string;
}

interface Props {
  endpoint: string;
}

const DowntimeDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { toDateTime } = useLocaleDateTimeFormat();

  const columns = [
    {
      getContent: ({ author_name }): string => author_name,
      id: 'author',
      label: t(labelAuthor),
      type: ColumnType.string,
      width: 100,
    },
    {
      getContent: ({ is_fixed }): string => t(getYesNoLabel(is_fixed)),
      id: 'is_fixed',
      label: t(labelFixed),
      type: ColumnType.string,
      width: 100,
    },
    {
      getContent: ({ start_time }): string => toDateTime(start_time),
      id: 'start_time',
      label: t(labelStartTime),
      type: ColumnType.string,
      width: 150,
    },
    {
      // eslint-disable-next-line react/no-unstable-nested-components
      getContent: ({ end_time }: DowntimeDetails): JSX.Element => (
        <span>{toDateTime(end_time)}</span>
      ),
      id: 'end_time',
      label: t(labelEndTime),
      type: ColumnType.string,
      width: 150,
    },

    {
      className: classes.comment,

      // eslint-disable-next-line react/no-unstable-nested-components
      getContent: ({ comment }: DowntimeDetails): JSX.Element => {
        return (
          <span className={classes.comment}>
            {parse(DOMPurify.sanitize(comment))}
          </span>
        );
      },
      id: 'comment',
      label: t(labelComment),
      type: ColumnType.component,
      width: 250,
    },
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

export default DowntimeDetailsTable;
