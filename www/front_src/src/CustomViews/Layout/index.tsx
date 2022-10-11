import { FC } from 'react';

import { Responsive } from '@visx/visx';
import GridLayout, { WidthProvider } from 'react-grid-layout';
import { useAtom, useAtomValue } from 'jotai';
import { find, map, propEq } from 'ramda';

import { Responsive as ResponsiveHeight } from '@centreon/ui';

import { columnsAtom, isEditingAtom, layoutAtom } from '../atoms';

import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

import EditionGrid from './EditionGrid';
import Widget from './Widget';

const ReactGridLayout = WidthProvider(GridLayout);

const Layout: FC = () => {
  const [layout, setLayout] = useAtom(layoutAtom);
  const isEditing = useAtomValue(isEditingAtom);
  const columns = useAtomValue(columnsAtom);

  const changeLayout = (newLayout): void => {
    setLayout((currentLayout) =>
      map(({ i, ...other }) => {
        const widget = find(propEq('i', i), currentLayout);

        return {
          ...other,
          i,
          widgetConfiguration: widget?.widgetConfiguration,
        };
      }, newLayout),
    );
  };

  return (
    <ResponsiveHeight>
      <Responsive.ParentSize>
        {({ width, height }): JSX.Element => (
          <>
            {isEditing && <EditionGrid height={height} width={width} />}
            <ReactGridLayout
              cols={columns}
              containerPadding={[0, 0]}
              layout={layout}
              resizeHandles={['s', 'w', 'e', 'n', 'sw', 'nw', 'se', 'ne']}
              rowHeight={30}
              width={width}
              onLayoutChange={changeLayout}
            >
              {layout.map(({ i, widgetConfiguration }) => {
                return (
                  <div key={i}>
                    <Widget key={i} path={widgetConfiguration.path} title={i} />
                  </div>
                );
              })}
            </ReactGridLayout>
          </>
        )}
      </Responsive.ParentSize>
    </ResponsiveHeight>
  );
};

export default Layout;
